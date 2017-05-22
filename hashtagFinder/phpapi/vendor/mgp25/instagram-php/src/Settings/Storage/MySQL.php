<?php

namespace InstagramAPI\Settings\Storage;

use InstagramAPI\Settings\StorageInterface;
use InstagramAPI\Exception\SettingsException;
use PDO;

/**
 * Persistent storage backend which uses a MySQL server.
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class MySQL implements StorageInterface
{
    /** @var \PDO Our connection to the database. */
    private $_pdo;

    /** @var bool Whether we own the PDO connection or are borrowing it. */
    private $_isSharedPDO;

    /** @var string Which table to store the settings in. */
    private $_dbTableName;

    /** @var string Current Instagram username that all settings belong to. */
    private $_username;

    /** @var array A cache of important columns from the user's database row. */
    private $_cache;

    /**
     * Connect to a storage location and perform necessary startup preparations.
     *
     * {@inheritDoc}
     */
    public function openLocation(
        array $locationConfig)
    {
        $this->_dbTableName = (isset($locationConfig['dbtablename'])
                               ? $locationConfig['dbtablename']
                               : 'user_sessions');

        if (isset($locationConfig['pdo'])) {
            // Pre-provided connection to re-use instead of creating a new one.
            if (!$locationConfig['pdo'] instanceof PDO) {
                throw new SettingsException('The custom PDO object is invalid.');
            }
            $this->_isSharedPDO = true;
            $this->_pdo = $locationConfig['pdo'];
        } else {
            // We should connect for the user, by creating our own PDO object.
            $username = ($locationConfig['dbusername'] ? $locationConfig['dbusername'] : 'root');
            $password = ($locationConfig['dbpassword'] ? $locationConfig['dbpassword'] : '');
            $host = ($locationConfig['dbhost'] ? $locationConfig['dbhost'] : 'localhost');
            $dbName = ($locationConfig['dbname'] ? $locationConfig['dbname'] : 'instagram');
            try {
                $this->_isSharedPDO = false;
                $this->_pdo = new PDO("mysql:host={$host};dbname={$dbName}",
                                      $username, $password);
            } catch (\Exception $e) {
                throw new SettingsException('MySQL Connection Failed: '.$e->getMessage());
            }
        }

        $this->_configurePDO();
        $this->_autoCreateTable();
    }

    /**
     * Configures the connection for our needs.
     *
     * Warning for those who re-used a PDO object: Beware that we WILL change
     * attributes on the PDO connection to suit our needs! Primarily turning all
     * error reporting into exceptions, and setting the charset to UTF-8. If you
     * want to re-use a PDO connection, you MUST accept the fact that WE NEED
     * exceptions and UTF-8 in our PDO! If that is not acceptable to you then DO
     * NOT re-use your own PDO object!
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    private function _configurePDO()
    {
        try {
            $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_pdo->query('SET NAMES UTF8');
        } catch (\Exception $e) {
            throw new SettingsException('MySQL Configuration Failed: '.$e->getMessage());
        }
    }

    /**
     * Automatically creates the MySQL storage table if necessary.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    private function _autoCreateTable()
    {
        try {
            // Detect the name of the MySQL database that PDO is connected to.
            $dbName = $this->_pdo->query('SELECT database()')->fetchColumn();

            // Abort if we already have the necessary table.
            $sth = $this->_pdo->prepare('SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = :tableSchema) AND (TABLE_NAME = :tableName)');
            $sth->execute([':tableSchema' => $dbName, ':tableName' => $this->_dbTableName]);
            $result = $sth->fetchColumn();
            $sth->closeCursor();
            if ($result > 0) {
                return;
            }

            // Create the database table. Throws in case of failure.
            // NOTE: We store all settings as a JSON blob so that we support all
            // current and future data without having to alter the table schema.
            $this->_pdo->exec('CREATE TABLE `'.$this->_dbTableName."` (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL,
                settings MEDIUMBLOB NULL,
                cookies MEDIUMBLOB NULL,
                last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY (username)
            ) COLLATE='utf8_general_ci' ENGINE=InnoDB;");
        } catch (\Exception $e) {
            throw new SettingsException('MySQL Error: '.$e->getMessage());
        }
    }

    /**
     * Automatically writes to the correct user's row and caches the new value.
     *
     * @param string $column The database column.
     * @param string $data   Data to be written.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    private function _setUserColumn(
        $column,
        $data)
    {
        if ($column != 'settings' && $column != 'cookies') {
            throw new SettingsException(sprintf(
                'Attempt to write to illegal database column "%s".',
                $column
            ));
        }

        try {
            // Update if the user row already exists, otherwise insert.
            $binds = [':data' => $data];
            if ($this->_cache['id'] !== null) {
                $sql = "UPDATE `{$this->_dbTableName}` SET {$column}=:data WHERE (id=:id)";
                $binds[':id'] = $this->_cache['id'];
            } else {
                $sql = "INSERT INTO `{$this->_dbTableName}` (username, {$column}) VALUES (:username, :data)";
                $binds[':username'] = $this->_username;
            }

            $sth = $this->_pdo->prepare($sql);
            $sth->execute($binds);

            // Keep track of the database row ID for the user.
            if ($this->_cache['id'] === null) {
                $this->_cache['id'] = $this->_pdo->lastinsertid();
            }

            $sth->closeCursor();

            // Cache the new value.
            $this->_cache[$column] = $data;
        } catch (\Exception $e) {
            throw new SettingsException('MySQL Error: '.$e->getMessage());
        }
    }

    /**
     * Whether the storage backend contains a specific user.
     *
     * {@inheritDoc}
     */
    public function hasUser(
        $username)
    {
        // Check whether a row exists for that username.
        $sth = $this->_pdo->prepare("SELECT EXISTS(SELECT 1 FROM `{$this->_dbTableName}` WHERE (username=:username))");
        $sth->execute([':username' => $username]);
        $result = $sth->fetchColumn();
        $sth->closeCursor();
        return ($result > 0 ? true : false);
    }

    /**
     * Move the internal data for a username to a new username.
     *
     * {@inheritDoc}
     */
    public function moveUser(
        $oldUsername,
        $newUsername)
    {
        try {
            // Verify that the old username exists.
            if (!$this->hasUser($oldUsername)) {
                throw new SettingsException(sprintf(
                    'Cannot move non-existent user "%s".',
                    $oldUsername
                ));
            }

            // Verify that the new username does not exist.
            if ($this->hasUser($newUsername)) {
                throw new SettingsException(sprintf(
                    'Refusing to overwrite existing user "%s".',
                    $newUsername
                ));
            }

            // Now attempt to rename the old username column to the new name.
            $sth = $this->_pdo->prepare("UPDATE `{$this->_dbTableName}` SET username=:newusername WHERE (username=:oldusername)");
            $sth->execute([':oldusername' => $oldUsername, ':newusername' => $newUsername]);
            $sth->closeCursor();
        } catch (SettingsException $e) {
            throw $e; // Ugly but necessary to re-throw only our own messages.
        } catch (\Exception $e) {
            throw new SettingsException('MySQL Error: '.$e->getMessage());
        }
    }

    /**
     * Delete all internal data for a given username.
     *
     * {@inheritDoc}
     */
    public function deleteUser(
        $username)
    {
        try {
            // Just attempt to delete the row. Doesn't error if already missing.
            $sth = $this->_pdo->prepare("DELETE FROM `{$this->_dbTableName}` WHERE (username=:username)");
            $sth->execute([':username' => $username]);
            $sth->closeCursor();
        } catch (\Exception $e) {
            throw new SettingsException('MySQL Error: '.$e->getMessage());
        }
    }

    /**
     * Open the data storage for a specific user.
     *
     * {@inheritDoc}
     */
    public function openUser(
        $username)
    {
        $this->_username = $username;

        // Retrieve and cache the existing user data row if available.
        try {
            $sth = $this->_pdo->prepare("SELECT id, settings, cookies FROM `{$this->_dbTableName}` WHERE (username=:username)");
            $sth->execute([':username' => $this->_username]);
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            $sth->closeCursor();

            if (is_array($result)) {
                $this->_cache = $result;
            } else {
                $this->_cache = [
                    'id'       => null,
                    'settings' => null,
                    'cookies'  => null,
                ];
            }
        } catch (\Exception $e) {
            throw new SettingsException('MySQL Error: '.$e->getMessage());
        }
    }

    /**
     * Load all settings for the currently active user.
     *
     * {@inheritDoc}
     */
    public function loadUserSettings()
    {
        $userSettings = [];

        if (!empty($this->_cache['settings'])) {
            $userSettings = @json_decode($this->_cache['settings'], true, 512, JSON_BIGINT_AS_STRING);
            if (!is_array($userSettings)) {
                throw new SettingsException(sprintf(
                    'Failed to decode corrupt settings for account "%s".',
                    $this->_username
                ));
            }
        }

        return $userSettings;
    }

    /**
     * Save the settings for the currently active user.
     *
     * {@inheritDoc}
     */
    public function saveUserSettings(
        array $userSettings,
        $triggerKey)
    {
        // Store the settings as a JSON blob.
        $encodedData = json_encode($userSettings);
        $this->_setUserColumn('settings', $encodedData);
    }

    /**
     * Whether the storage backend has cookies for the currently active user.
     *
     * {@inheritDoc}
     */
    public function hasUserCookies()
    {
        return (isset($this->_cache['cookies'])
                && !empty($this->_cache['cookies']));
    }

    /**
     * Load all cookies for the currently active user.
     *
     * {@inheritDoc}
     */
    public function loadUserCookies()
    {
        return (isset($this->_cache['cookies'])
                ? $this->_cache['cookies']
                : null );
    }

    /**
     * Save all cookies for the currently active user.
     *
     * {@inheritDoc}
     */
    public function saveUserCookies(
        $rawData)
    {
        // Store the raw cookie data as-provided.
        $this->_setUserColumn('cookies', $rawData);
    }

    /**
     * Close the settings storage for the currently active user.
     *
     * {@inheritDoc}
     */
    public function closeUser()
    {
        $this->_username = null;
        $this->_cache = null;
    }

    /**
     * Disconnect from a storage location and perform necessary shutdown steps.
     *
     * {@inheritDoc}
     */
    public function closeLocation()
    {
        // Delete our reference to the PDO object. If nobody else references
        // it, the MySQL connection will now be terminated. In case of shared
        // objects, the original owner still has their reference (as intended).
        $this->_pdo = null;
    }
}

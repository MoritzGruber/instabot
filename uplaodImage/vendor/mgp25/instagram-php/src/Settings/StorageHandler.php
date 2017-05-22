<?php

namespace InstagramAPI\Settings;

use InstagramAPI\Exception\SettingsException;

/**
 * Advanced, modular settings storage engine.
 *
 * Connects to a StorageInterface and transfers data to/from the application,
 * with intelligent caching and data translation.
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class StorageHandler
{
    /**
     * Complete list of all settings that will be stored/retrieved persistently.
     *
     * This key list WILL be changed whenever we need to support new features,
     * so do NOT assume that it will stay the same forever.
     *
     * @var array
     */
    const PERSISTENT_KEYS = [
        'account_id', // The numerical UserPK ID of the account.
        'devicestring', // Which Android device they're identifying as.
        'device_id',
        'phone_id',
        'uuid',
        'token',
        'last_login',
    ];

    /** @var StorageInterface The active storage backend. */
    private $_storage;

    /** @var string Current Instagram username that all settings belong to. */
    private $_username;

    /** @var array Cache for the current user's key-value settings pairs. */
    private $_userSettings;

    /**
     * Constructor.
     *
     * @param StorageInterface $storageInterface An instance of desired Storage.
     * @param array            $locationConfig   Configuration parameters for
     *                                           the storage backend location.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function __construct(
        $storageInstance,
        array $locationConfig = [])
    {
        if (!$storageInstance instanceof StorageInterface) {
            throw new SettingsException(
                'You must provide an instance of a StorageInterface class.'
            );
        }
        if (!is_array($locationConfig)) {
            throw new SettingsException(
                'The storage location configuration must be an array.'
            );
        }

        // Connect the storage instance to the user's desired storage location.
        $this->_storage = $storageInstance;
        $this->_storage->openLocation($locationConfig);
    }

    /**
     * Destructor.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function __destruct()
    {
        // The storage handler is being killed, so tell the location to close.
        if ($this->_username !== null) {
            $this->_storage->closeUser();
            $this->_username = null;
        }
        $this->_storage->closeLocation();
    }

    /**
     * Whether the storage backend contains a specific user.
     *
     * @param string $username The Instagram username.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     *
     * @return bool TRUE if user exists, otherwise FALSE.
     */
    public function hasUser(
        $username)
    {
        $this->_throwIfEmptyValue($username);

        return $this->_storage->hasUser($username);
    }

    /**
     * Move the internal data for a username to a new username.
     *
     * This function is important because of the fact that all per-user settings
     * in all Storage implementations are retrieved and stored via its Instagram
     * username, since their NAME is literally the ONLY thing we know about a
     * user before we have loaded their settings or logged in! So if you later
     * rename that Instagram account, it means that your old device settings
     * WON'T follow along automatically, since the new login username is seen
     * as a brand new user that isn't in the settings storage.
     *
     * This function conveniently tells your chosen Storage backend to move a
     * user's settings to a new name, so that they WILL be found again when you
     * later look for settings for your new name.
     *
     * Bonus guide for easily confused people: YOU must manually rename your
     * user on Instagram.com before you call this function. We don't do that.
     *
     * @param string $oldUsername The old name that settings are stored as.
     * @param string $newUsername The new name to move the settings to.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function moveUser(
        $oldUsername,
        $newUsername)
    {
        $this->_throwIfEmptyValue($oldUsername);
        $this->_throwIfEmptyValue($newUsername);

        if ($oldUsername === $this->_username
            || $newUsername === $this->_username) {
            throw new SettingsException(
                'Attempted to move settings to/from the currently active user.'
            );
        }

        $this->_storage->moveUser($oldUsername, $newUsername);
    }

    /**
     * Delete all internal data for a given username.
     *
     * @param string $username The Instagram username.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function deleteUser(
        $username)
    {
        $this->_throwIfEmptyValue($username);

        if ($username === $this->_username) {
            throw new SettingsException(
                'Attempted to delete the currently active user.'
            );
        }

        $this->_storage->deleteUser($username);
    }

    /**
     * Load all settings for a user from the storage and mark as current user.
     *
     * @param string $username The Instagram username.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function setActiveUser(
        $username)
    {
        $this->_throwIfEmptyValue($username);

        // If that user is already loaded, there's no need to do anything.
        if ($username === $this->_username) {
            return;
        }

        // If we're switching away from a user, tell the backend to close the
        // current user's storage (if it needs to do any special processing).
        if ($this->_username !== null) {
            $this->_storage->closeUser();
        }

        // Set the new user as the current user for this storage instance.
        $this->_username = $username;
        $this->_userSettings = [];
        $this->_storage->openUser($username);

        // Retrieve any existing settings for the user from the backend.
        $loadedSettings = $this->_storage->loadUserSettings();
        foreach ($loadedSettings as $key => $value) {
            // Map renamed old-school v1.x keys to new v2.x key names.
            if ($key == 'username_id') {
                $key = 'account_id';
            }

            // Only keep values for keys that are still in use. Discard others.
            if (in_array($key, self::PERSISTENT_KEYS)) {
                // Cast all values to strings to ensure we only use strings!
                // NOTE: THIS CAST IS EXTREMELY IMPORTANT AND *MUST* BE DONE!
                $this->_userSettings[$key] = (string) $value;
            }
        }
    }

    /**
     * Does a preliminary guess about whether the current user is logged in.
     *
     * Can only be executed after setActiveUser(). And the session it looks
     * for may be expired, so there's no guarantee that we are still logged in.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     *
     * @return bool TRUE if possibly logged in, otherwise FALSE.
     */
    public function isMaybeLoggedIn()
    {
        $this->_throwIfNoActiveUser();

        return ($this->_storage->hasUserCookies()
                && !empty($this->get('account_id'))
                && !empty($this->get('token')));
    }

    /**
     * Retrieve the value of a setting from the current user's memory cache.
     *
     * Can only be executed after setActiveUser().
     *
     * @param string $key Name of the setting.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     *
     * @return string|null The value as a string IF the setting exists AND is
     *                     a NON-EMPTY string. Otherwise NULL.
     */
    public function get(
        $key)
    {
        $this->_throwIfNoActiveUser();

        // Reject anything that isn't in our list of VALID persistent keys.
        if (!in_array($key, self::PERSISTENT_KEYS)) {
            throw new SettingsException(sprintf(
                'The settings key "%s" is not a valid persistent key name.',
                $key
            ));
        }

        // Return value if it's a NON-EMPTY string, otherwise return NULL.
        // NOTE: All values are cached as strings so no casting is needed.
        return ((isset($this->_userSettings[$key])
                 && $this->_userSettings[$key] !== '')
                ? $this->_userSettings[$key]
                : null);
    }

    /**
     * Store a setting's value for the current user.
     *
     * Can only be executed after setActiveUser(). To clear the value of a
     * setting, simply pass in an empty string as value.
     *
     * @param string       $key   Name of the setting.
     * @param string|mixed $value The data to store. MUST be castable to string.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function set(
        $key,
        $value)
    {
        $this->_throwIfNoActiveUser();

        // Reject anything that isn't in our list of VALID persistent keys.
        if (!in_array($key, self::PERSISTENT_KEYS)) {
            throw new SettingsException(sprintf(
                'The settings key "%s" is not a valid persistent key name.',
                $key
            ));
        }

        // Reject null values, since they may be accidental. To unset a setting,
        // the caller must explicitly pass in an empty string instead.
        if ($value === null) {
            throw new SettingsException(
                'Illegal attempt to store null value in settings storage.'
            );
        }

        // Cast the value to string to ensure we don't try writing non-strings.
        // NOTE: THIS CAST IS EXTREMELY IMPORTANT AND *MUST* ALWAYS BE DONE!
        $value = (string) $value;

        // Check if the value differs from our storage (cached representation).
        // NOTE: This optimizes writes by only writing when values change!
        if (!array_key_exists($key, $this->_userSettings)
            || $this->_userSettings[$key] !== $value) {
            // The value differs, so save to memory cache and write to storage.
            $this->_userSettings[$key] = $value;
            $this->_storage->saveUserSettings($this->_userSettings, $key);
        }
    }

    /**
     * Whether the storage backend has cookies for the currently active user.
     *
     * Can only be executed after setActiveUser().
     *
     * @throws \InstagramAPI\Exception\SettingsException
     *
     * @return bool TRUE if cookies exist, otherwise FALSE.
     */
    public function hasCookies()
    {
        $this->_throwIfNoActiveUser();

        return $this->_storage->hasUserCookies();
    }

    /**
     * Get all cookies for the currently active user.
     *
     * Can only be executed after setActiveUser().
     *
     * @throws \InstagramAPI\Exception\SettingsException
     *
     * @return array Cookies with their "format" ("cookiefile", "cookiestring")
     *               and a "data" field pointing to the file (if "cookiefile")
     *               or containing the raw cookie data (if "cookiestring"). The
     *               cookiestring will be an empty string if no cookies exist.
     */
    public function getCookies()
    {
        $this->_throwIfNoActiveUser();

        // Load and parse the cookie format.
        $cookieFormat = 'cookiestring'; // Assume regular raw cookie-string.
        $cookieData = $this->_storage->loadUserCookies();
        if (!is_string($cookieData)) {
            $cookieData = ''; // No cookies exist.
        }
        if (strncmp($cookieData, 'cookiefile:', 11) === 0) {
            $cookieFormat = 'cookiefile';
            $cookieData = substr($cookieData, strpos($cookieData, ':') + 1);
        }

        return [
            'format' => $cookieFormat,
            'data'   => $cookieData,
        ];
    }

    /**
     * Save all cookies for the currently active user.
     *
     * Can only be executed after setActiveUser(). Note that this function is
     * called frequently! But it is ONLY called if a non-"cookiefile" answer
     * was returned by the getCookies() call.
     *
     * @param string $rawData An encoded string with all cookie data.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function setCookies(
        $rawData)
    {
        $this->_throwIfNoActiveUser();
        $this->_throwIfNotString($rawData);

        return $this->_storage->saveUserCookies($rawData);
    }

    /**
     * Internal: Ensures that a parameter is a string.
     *
     * @param mixed $value The value to check.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _throwIfNotString(
        $value)
    {
        if (!is_string($value)) {
            throw new SettingsException('Parameter must be string.');
        }
    }

    /**
     * Internal: Ensures that a parameter is a non-empty string.
     *
     * @param mixed $value The value to check.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _throwIfEmptyValue(
        $value)
    {
        if (!is_string($value) || $value === '') {
            throw new SettingsException('Parameter must be non-empty string.');
        }
    }

    /**
     * Internal: Ensures that there is an active storage user.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _throwIfNoActiveUser()
    {
        if ($this->_username === null) {
            throw new SettingsException(
                "Called user-related function before setting the current storage user."
            );
        }
    }
}

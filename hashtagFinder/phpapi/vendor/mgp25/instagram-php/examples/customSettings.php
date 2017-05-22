<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$debug = true;
$truncatedDebug = false;
//////////////////////

/*
 * This example demonstrates how to use and customize user settings storages.
 *
 * By default, if you don't give us any custom configuration, we will always use
 * the "File" storage backend, which keeps all data in regular files on disk. It
 * is a rock-solid backend and will be very good for most people.
 *
 * However, other people may want to use something more advanced, such as one of
 * the other built-in storage backends ("MySQL" and "Memcached"). Or perhaps you
 * would even like to build your own backend (doing so is very easy).
 */

echo "You are not supposed to execute this script. Read it in a text editor to see various storage methods.\n";
exit;

// These points will give you a basic overview of the process. But you should
// read the code in src/Settings/ for the full details. It is well documented.

// 1. Choosing a built-in storage backend (one of "file", "mysql", "memcached"),
// and using the automatic, default settings for that storage backend:
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug, ['storage' => 'mysql']);

// 2. You can read src/Settings/Factory.php for valid settings for each backend.
// Here's an example of how to change the default storage location for "file":
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug, [
    'storage'    => 'file',
    'basefolder' => 'some/path/',
]);

// 3. If you read src/Settings/Factory.php, you'll notice that you can choose
// the storage backends and most of their parameters via the command line or
// environment variables instead. For example: "SETTINGS_STORAGE=mysql php
// yourscript.php" would set the "storage" parameter via the environment, and
// typing "php yourscript.php --settings_storage=mysql" would set it via the
// command line. The command-line arguments have the highest precedence, then
// the environment variables, and lastly the code within your script. This
// precedence order is so that you can easily override your script's code to
// test other backends or change their parameters without modifying your code.

// 4. Very advanced users can look in src/Settings/StorageHandler.php to read
// about hasUser(), moveUser() and deleteUser(). Three very, VERY DANGEROUS
// commands which let you rename or delete account settings in your storage.
// Carefully read through their descriptions and use them wisely. If you're sure
// that you dare to use them, then you can access them via $ig->storage->...

// 5. Lastly... if you want to implement your own completely custom storage,
// then you simply have to do one thing: Implement the StorageInterface class
// interface. But be very sure to STRICTLY follow ALL rules for storage backends
// described in that interface's docs, otherwise your custom backend WON'T work.
//
// See the overview in src/Settings/StorageInterface.php, and then read through
// the various built-in storage backends in src/Settings/Storage/ to see perfect
// implementations that completely follow the required interface specification.
//
// To use your custom storage backend, you would simply create your own class
// similar to the built-in backends. But do NOT put your own class in our
// src/Settings/Storage/ folder. Store your class inside your own project.
//
// Then simply provide your custom storage class instance as the storage class:
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug, [
    'storage' => 'custom',
    'class'   => new MyCustomStorage(), // Whatever you've named your class.
]);

// That's it! This should get you started on your journey. :-)

// And please think about contributing your WELL-WRITTEN storage backends to
// this project! If you had a reason to write your own, then there's probably
// someone else out there with the same need. Remember to SHARE with the open
// source community! ;-)

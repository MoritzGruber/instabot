<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

/////// MEDIA ////////
$videoFilename = '';
$captionText = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // Note that this performs a few automatic chunk upload retries by default,
    // in case of failing to upload the video chunks to Instagram's server!
    $ig->uploadTimelineVideo($videoFilename, ['caption' => $captionText]);

    // or...

    // Example of using 8 retries instead of the default amount:
    // $ig->uploadTimelineVideo($videoFilename, ['caption' => $captionText], 8);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

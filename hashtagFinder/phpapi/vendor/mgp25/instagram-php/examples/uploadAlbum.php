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

/**
 * Uploading a timeline album (aka carousel aka sidecar).
 */

/////// MEDIA ////////
$media = [ // Albums can contain between 2 and 10 photos/videos.
    [
        'type'     => 'photo',
        'file'     => '', // Path to the photo file.
    ],
    [
        'type'     => 'photo',
        'file'     => '', // Path to the photo file.
        /* TAGS COMMENTED OUT UNTIL YOU READ THE BELOW:
        'usertags' => [ // Optional, lets you tag one or more users in a PHOTO.
            [
                'position' => [0.5, 0.5],

                // WARNING: THE USER ID MUST BE VALID. INSTAGRAM WILL VERIFY IT
                // AND IF IT'S WRONG THEY WILL SAY "media configure error".
                'user_id'  => '123456789', // Must be a numerical UserPK ID.
            ],
        ],
        */
    ],
    [
        'type'     => 'video',
        'file'     => '', // Path to the video file.
    ],
];
$captionText = ''; // Caption text to use for the album.
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
    $ig->uploadTimelineAlbum($media, ['caption' => $captionText]);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

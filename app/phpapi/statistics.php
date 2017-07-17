<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';

/////// CONFIG ///////
$targetname = $argv[1];

$str = file_get_contents('./../config.json');
$jsonConfig= json_decode($str, true);

$username = $jsonConfig['username'];
$password = $jsonConfig['password'];
$debug = false;
$truncatedDebug = false;
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

    $obj = array();

    $user = $ig->getUserInfoByName($targetname);
    $userid = $user->user->pk;
    $feed = $ig->getUserFeed($userid);
    $feed_items = $feed->items;

    $likes = 0;
    $comments = 0;

    $obj["timestamp"] = time();
    $obj["username"] = $user->user->username;
    $obj["follower_count"] = $user->user->follower_count;
    $obj["following_count"] = $user->user->following_count;
    $obj["media_count"] = $user->user->media_count;
    $obj["usertags_count"] = $user->user->usertags_count;
    $obj["feed_items"] = count($feed->items);

    do
    {
        foreach ($feed_items as $value)
        {
            $likes = $likes + $value->like_count;
            $comments = $comments + $value->comment_count;
        }

        if ($feed->more_available)
        {
            $feed = $ig->getUserFeed($userid, $feed->next_max_id);
            $feed_items = $feed->items;
        }
    } while ($feed->more_available);

    $obj["likes"] = $likes;
    $obj["comments"] = $comments;

    echo json_encode($obj);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

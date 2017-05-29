<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';

/////// CONFIG ///////
$targetname = $argv[1];

$username = 'praisingofcars';
$password = 'clubmate123';
$debug = false;
$truncatedDebug = false;
$file_path = basename(dirname($_SERVER['PHP_SELF'])) . '/resources/userinformation.csv';
$fields = array(
    "timestamp" => "timestamp",
    "username" => "username",
    "follower_count" => "follower_count",
    "following_count" => "following_count",
    "media_count" => "media_count",
    "usertags_count" => "usertags_count",
    "feed_items" => "feed_items",
    "likes" => "likes",
    "comments" => "comments"
);
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

    if ( 0 == filesize( $file_path ) )
    {
        // file is empty

        $count = 1;
        foreach ($fields as $value)
        {
            echo $value;

            if (count($fields) != $count) echo ",";
            else echo "\r\n";

            $count = $count + 1;
        }
    }

    $user = $ig->getUserInfoByName($targetname);
    $userid = $user->user->pk;
    //$likedmedia = $ig->getLikedMedia();
    //$proxy = $ig->getProxy();
    $feed = $ig->getUserFeed($userid);
    $feed_items = $feed->items;

    $likes = 0;
    $comments = 0;

    echo time() . ",";
    echo $user->user->username . ",";
    echo $user->user->follower_count . ",";
    echo $user->user->following_count . ",";
    echo $user->user->media_count . ",";
    echo $user->user->usertags_count . ",";

    echo count($feed->items) . ",";

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

    // TODO: in einer for-schleife alle feed_items durchstreifen und Likes + Kommentare hochzählen + ausgeben
    /*foreach ($feed_items as $value)
    {
        $likes = $likes + $value->like_count;
        $comments = $comments + $value->comment_count;
    }

    if ($feed->more_available)
    {
        $feed = $ig->getUserFeed($userid, $feed->next_max_id);
        $feed_items = $feed->items;
    }

    foreach ($feed_items as $value)
    {
        $likes = $likes + $value->like_count;
        $comments = $comments + $value->comment_count;
    }*/

    echo $likes . ",";
    echo $comments;

    echo "\r\n";
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';

/////// CONFIG ///////
$topic = $argv[1];
$maxpp = $argv[2];
$num = $argv[3];
$username = 'praisingofcars';
$password = 'clubmate123';
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

    /*if ( 0 == filesize( $file_path ) )
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
    }*/

    $tagsResult = $ig->getHashtagFeed($topic);
    $tags = $tagsResult->ranked_items;

    // array_values($tags)[0];
    
    // echo "Results: " . $tagsResult->num_results . ",\r\n";
    // echo $topic . ",\r\n";
    // echo array_values($tags)[0]->name;
    
    $results = array();

    foreach ($tags as $value)
    {
        $commentsResp = $ig->getMediaComments($value->id);
        $comments = $commentsResp->comments;
        if ($commentsResp->comment_count > 0) {

            $imgCommentCounter = 0;
            foreach ($comments as $comment)
            {
                if (count($results) < $num) {
                    if ($imgCommentCounter < $maxpp) {
                        $commentText = $comment->text;
                        $results[] = $commentText;
                        $imgCommentCounter = $imgCommentCounter + 1;
                    } else {
                        break;
                    }
                } else {
                    echo json_encode($results,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                    echo "\r\n";
                    return;
                }
            }
        }
    }

    echo json_encode($results,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    echo "\r\n";
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
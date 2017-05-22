
<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';
/////// CONFIG ///////



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

    $tag = $argv[1];
    //\InstagramAPI\Response\SearchTagResponse
    $related = $ig->getTagRelated($tag);
    $newHashtagsArray = array();
    foreach ($related->related as $hashtag) {
        if ($hashtag->type == "hashtag"){
                    array_push($newHashtagsArray, $hashtag->name);
        }
    }
    echo implode(" ",$newHashtagsArray);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

<?php
set_time_limit(60);
date_default_timezone_set('UTC');
require __DIR__ . '/vendor/autoload.php';
/////// CONFIG /////// or $argv[3] == null or $argv[4] == null
if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Usage : \"php main.php username password filename somecaption\"\n";
    exit(0);
}
$username = $argv[1];
$password = $argv[2];

$debug = false;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
    exit(0);
}
try {
    //load images data from grabImages Service
    $str = file_get_contents('./savedStatus/images.json');
    $jsonImages = json_decode($str, true);
    //load blacklist
    if (!is_file('./savedStatus/blacklist.json')) {
        exec("touch ./savedStatus/blacklist.json");
    }
    $file = "./savedStatus/blacklist.json";
    $jsonBlacklist = json_decode(file_get_contents($file));
    // get total number of images
    $totalNumberOfImages = $jsonImages['total_results'];
    // pick a random image and check if this image is blacklisted

    if (is_array($jsonBlacklist)) {
        //counter to check if we run out of images
        $counter = $totalNumberOfImages +1;
        while (1) {
            $imageToPick = rand(0, $totalNumberOfImages);
            if (!in_array($jsonImages['photos'][$imageToPick]['id'], $jsonBlacklist)){
                //we break the loop if the rand number is not in the blacklist
                break;
            }
            //exit with error when we all images are used
            if($counter < 0){
                exit(404);
            } else{
                $counter--;
            }

        }
    } else {
        //no blackist exists, so we can just pick any image
        $imageToPick = rand(0, $totalNumberOfImages);
    }


    $image = $jsonImages['photos'][$imageToPick];
    //creating dir if it doesn't exist
    if (!is_dir('images/')) {
        exec("mkdir images");
    }
    $dest = "images/" . (string)$image['id'] . ".jpg";
    echo "Picking image with id " . $imageToPick . " and saving to " . $dest."\n";

    copy($image['src'], $dest);


    //get up to 30 random hashtags of the hashtag file

    $jsonHashtags = json_decode(file_get_contents('./savedStatus/hashtags.json'));
    # get 20-30 random numbers of the big hashtag list
    $arrayOO = array_rand ( $jsonHashtags, rand (3 , 10));
    # fill caption var
    $caption = "";
    foreach ($arrayOO as $value){
        $caption = $caption.$jsonHashtags[$value]." ";
    }
    echo $caption."\n";
    $metadata = ['caption' => $caption];
    #get photo by id
    $photoFile = "images/".(string)$image['id'].'.jpg';
    $thumb = imagecreatetruecolor(800,800);
    imagecopyresized($thumb, imagecreatefromjpeg($photoFile), 0, 0, 0, 0, 800, 800, 1200, 1200);
    imagejpeg($thumb, $photoFile);
    $ig->uploadTimelinePhoto($photoFile, $metadata);

    //after upload write the image number we used to the blacklistId.json file


    if (!is_array($jsonBlacklist)) {
        $jsonBlacklist = array($image['id']);
    } else {
        array_push($jsonBlacklist, $image['id']);
    }

    file_put_contents($file, json_encode($jsonBlacklist));
    // then delete the image
    unlink($photoFile);

    echo "Successful \n";
    exit(0);


} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
    exit(0);
}
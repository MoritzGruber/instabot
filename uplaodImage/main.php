<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__ . '/vendor/autoload.php';
/////// CONFIG /////// or $argv[3] == null or $argv[4] == null
if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3]) || !isset($argv[4])) {
    echo "Usage : \"php main.php username password filename somecaption\"\n";
    exit(0);
}
$username =  $argv[1];
$password =  $argv[2];
$filename =  $argv[3];
$caption = $argv[4];
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
    $metadata = ['caption' => $caption];

    $photoFile = $filename;

    $ig->uploadTimelinePhoto($photoFile, $metadata);

    echo "Successful \n";
    exit(0);


} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
    exit(0);
}
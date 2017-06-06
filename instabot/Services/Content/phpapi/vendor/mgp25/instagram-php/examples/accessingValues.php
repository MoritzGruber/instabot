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

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $feed = $ig->getPopularFeed();

    // The getPopularFeed() has an "items" property, which we need.
    $items = $feed->getItems();

    // Individual item objects have an "id" property.
    $firstItem_mediaId = $items[0]->getId();

    // To get properties with underscores, such as "device_stamp",
    // just specify them as camelcase, ie "getDeviceTimestamp" below.
    $firstItem_device_timestamp = $items[0]->getDeviceTimestamp();

    // You can chain multiple function calls in a row to get to the data.
    $firstItem_image_versions = $items[0]->getImageVersions2()->getCandidates()[0]->getUrl();

    echo 'There are '.count($items)." items.\n";

    echo "First item has media id: {$firstItem_mediaId}.\n";
    echo "First item timestamp is: {$firstItem_device_timestamp}.\n";
    echo "One of the first item image version candidates is: {$firstItem_image_versions}.\n";
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

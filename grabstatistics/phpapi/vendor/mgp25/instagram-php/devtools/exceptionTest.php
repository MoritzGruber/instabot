<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

use InstagramAPI\Exception\ServerMessageThrower;

/*
 * Emulates various server message strings and verifies that they're mapped
 * correctly by the ServerMessageThrower's parser.
 */
$exceptionsToTest = [
    'InstagramAPI\\Exception\\LoginRequiredException'      => ['login_required'],
    'InstagramAPI\\Exception\\FeedbackRequiredException'   => ['feedback_required'],
    'InstagramAPI\\Exception\\CheckpointRequiredException' => ['checkpoint_required'],
    'InstagramAPI\\Exception\\IncorrectPasswordException'  => ['The password you entered is incorrect. Please try again.'],
    'InstagramAPI\\Exception\\AccountDisabledException'    => ['Your account has been disabled for violating our terms. Learn how you may be able to restore your account.'],
];

foreach ($exceptionsToTest as $exceptionClassName => $testMessages) {
    foreach ($testMessages as $testMessage) {
        try {
            ServerMessageThrower::autoThrow(null, $testMessage);
        } catch (\InstagramAPI\Exception\InstagramException $e) {
            $thisClassName = get_class($e);
            if ($exceptionClassName == $thisClassName) {
                echo "{$exceptionClassName}: OK!\n";
            } else {
                echo "{$exceptionClassName}: Got {$thisClassName} instead!\n";
            }
        }
    }
}

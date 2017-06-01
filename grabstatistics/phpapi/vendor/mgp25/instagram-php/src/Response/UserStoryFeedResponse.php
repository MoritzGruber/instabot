<?php

namespace InstagramAPI\Response;

class UserStoryFeedResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Broadcast
     */
    public $broadcast;
    /**
     * @var Model\Reel
     */
    public $reel;
}

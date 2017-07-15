<?php

namespace InstagramAPI\Response;

class ReelsTrayFeedResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Tray[]
     */
    public $tray;
    /**
     * @var Model\Broadcast[]
     */
    public $broadcasts;
}

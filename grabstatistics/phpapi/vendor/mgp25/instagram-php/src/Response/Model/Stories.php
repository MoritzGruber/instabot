<?php

namespace InstagramAPI\Response\Model;

class Stories extends \InstagramAPI\Response
{
    public $is_portrait;
    /**
     * @var Tray[]
     */
    public $tray;
    /**
     * @var string
     */
    public $id;
    /**
     * @var TopLive
     */
    public $top_live;
}

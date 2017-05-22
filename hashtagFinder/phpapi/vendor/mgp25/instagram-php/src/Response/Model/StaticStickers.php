<?php

namespace InstagramAPI\Response\Model;

class StaticStickers extends \InstagramAPI\Response
{
    public $include_in_recent;
    /**
     * @var string
     */
    public $id;
    /**
     * @var Stickers[]
     */
    public $stickers;
}

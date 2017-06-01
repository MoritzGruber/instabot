<?php

namespace InstagramAPI\Response;

class FBLocationResponse extends \InstagramAPI\Response
{
    public $has_more;
    /**
     * @var Model\LocationItem[]
     */
    public $items;
}

<?php

namespace InstagramAPI\Response\Model;

class StoryLocation extends \InstagramAPI\Response
{
    /**
     * @var float
     */
    public $rotation;
    /**
     * @var float
     */
    public $x;
    /**
     * @var float
     */
    public $y;
    /**
     * @var float
     */
    public $height;
    /**
     * @var float
     */
    public $width;
    /**
     * @var Location
     */
    public $location;
}

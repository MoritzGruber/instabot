<?php

namespace InstagramAPI\Response\Model;

class Story extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $pk;
    /**
     * @var Counts
     */
    public $counts;
    /**
     * @var Args
     */
    public $args;
    public $type;
}

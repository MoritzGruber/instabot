<?php

namespace InstagramAPI\Response\Model;

class ExploreItem extends \InstagramAPI\Response
{
    /**
     * @var Item
     */
    public $media;
    /**
     * @var Stories
     */
    public $stories;
    /**
     * @var Channel
     */
    public $channel;
}

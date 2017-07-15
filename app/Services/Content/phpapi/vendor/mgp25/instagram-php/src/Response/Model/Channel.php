<?php

namespace InstagramAPI\Response\Model;

class Channel extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $channel_id;
    public $channel_type;
    public $title;
    public $header;
    public $media_count;
    /**
     * @var Item
     */
    public $media;
    public $context;
}

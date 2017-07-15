<?php

namespace InstagramAPI\Response\Model;

class Caption extends \InstagramAPI\Response
{
    public $status;
    /**
     * @var string
     */
    public $user_id;
    public $created_at_utc;
    public $created_at;
    public $bit_flags;
    /**
     * @var User
     */
    public $user;
    public $content_type;
    public $text;
    /**
     * @var string
     */
    public $media_id;
    /**
     * @var string
     */
    public $pk;
    public $type;
    public $has_translation;
}

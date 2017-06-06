<?php

namespace InstagramAPI\Response\Model;

class Comment extends \InstagramAPI\Response
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
    /**
     * @var string
     */
    public $pk;
    public $text;
    public $content_type;
    public $type;
    public $comment_like_count;
    public $has_liked_comment;
    public $has_translation;
}

<?php

namespace InstagramAPI\Response;

class MediaCommentsResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Comment[]
     */
    public $comments;
    public $comment_count;
    public $comment_likes_enabled;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var Model\Caption
     */
    public $caption;
    public $has_more_comments;
    public $caption_is_edited;
    public $preview_comments;
    public $has_more_headload_comments;
}

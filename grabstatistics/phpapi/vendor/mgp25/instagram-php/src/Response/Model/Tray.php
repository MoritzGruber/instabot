<?php

namespace InstagramAPI\Response\Model;

class Tray extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var Item[]
     */
    public $items;
    /**
     * @var User
     */
    public $user;
    public $can_reply;
    public $expiring_at;
    public $seen_ranked_position;
    public $seen;
    public $latest_reel_media;
    public $ranked_position;
    public $is_nux;
    public $show_nux_tooltip;
    public $muted;
    public $prefetch_count;
    /**
     * @var Location
     */
    public $location;
    public $source_token;
}

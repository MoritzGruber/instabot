<?php

namespace InstagramAPI\Response;

class FollowingRecentActivityResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Story[]
     */
    public $stories;
    /**
     * @var string
     */
    public $next_max_id;
    public $auto_load_more_enabled;
    public $megaphone;
}

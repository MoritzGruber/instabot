<?php

namespace InstagramAPI\Response;

class TimelineFeedResponse extends \InstagramAPI\Response
{
    public $num_results;
    public $is_direct_v2_enabled;
    public $auto_load_more_enabled;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var Model\_Message[]
     */
    public $_messages;
    /**
     * @var Model\Item[]
     */
    public $feed_items;
    /**
     * @var Model\FeedAysf
     */
    public $megaphone;
}

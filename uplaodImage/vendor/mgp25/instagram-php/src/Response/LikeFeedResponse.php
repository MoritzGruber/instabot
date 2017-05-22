<?php

namespace InstagramAPI\Response;

class LikeFeedResponse extends \InstagramAPI\Response
{
    public $auto_load_more_enabled;
    /**
     * @var Model\Item[]
     */
    public $items;
    public $more_available;
    public $patches;
    public $last_counted_at;
    public $num_results;
    /**
     * @var string
     */
    public $next_max_id;
}

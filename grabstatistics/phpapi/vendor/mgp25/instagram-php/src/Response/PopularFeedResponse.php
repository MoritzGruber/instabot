<?php

namespace InstagramAPI\Response;

class PopularFeedResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $next_max_id;
    public $more_available;
    public $auto_load_more_enabled;
    /**
     * @var Model\Item[]
     */
    public $items;
    public $num_results;
    /**
     * @var string
     */
    public $max_id;
}

<?php

namespace InstagramAPI\Response;

class TagFeedResponse extends \InstagramAPI\Response
{
    public $num_results;
    /**
     * @var Model\Item[]
     */
    public $ranked_items;
    public $auto_load_more_enabled;
    /**
     * @var Model\Item[]
     */
    public $items;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
}

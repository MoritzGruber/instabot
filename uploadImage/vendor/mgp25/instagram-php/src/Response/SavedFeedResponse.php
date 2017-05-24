<?php

namespace InstagramAPI\Response;

class SavedFeedResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\SavedFeedItem[]
     */
    public $items;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
    public $auto_load_more_enabled;
    public $num_results;
}

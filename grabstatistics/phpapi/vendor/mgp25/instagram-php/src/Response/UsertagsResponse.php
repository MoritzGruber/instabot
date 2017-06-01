<?php

namespace InstagramAPI\Response;

class UsertagsResponse extends \InstagramAPI\Response
{
    public $num_results;
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
    public $total_count;
    public $requires_review;
    public $new_photos;
}

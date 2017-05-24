<?php

namespace InstagramAPI\Response;

class ExploreResponse extends \InstagramAPI\Response
{
    public $num_results;
    public $auto_load_more_enabled;
    /**
     * @var Model\ExploreItem[]
     */
    public $items;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var string
     */
    public $max_id;
    public $rank_token;
}

<?php

namespace InstagramAPI\Response;

class DiscoverTopLiveResponse extends \InstagramAPI\Response
{
    public $auto_load_more_enabled;
    /**
     * @var Model\BroadcastItem[]
     */
    public $broadcasts;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
}

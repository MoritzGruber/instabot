<?php

namespace InstagramAPI\Response;

class MediaInfoResponse extends \InstagramAPI\Response
{
    public $auto_load_more_enabled;
    public $status;
    public $num_results;
    public $more_available;
    /**
     * @var Model\Item[]
     */
    public $items;
}

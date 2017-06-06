<?php

namespace InstagramAPI\Response;

class LocationResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Location[]
     */
    public $venues;
    /**
     * @var string
     */
    public $request_id;
}

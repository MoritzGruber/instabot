<?php

namespace InstagramAPI\Response;

class RankedRecipientsResponse extends \InstagramAPI\Response
{
    public $expires;
    /**
     * @var Model\Users[]
     */
    public $ranked_recipients;
    public $filtered;
}

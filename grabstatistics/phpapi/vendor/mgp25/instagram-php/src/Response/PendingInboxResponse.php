<?php

namespace InstagramAPI\Response;

class PendingInboxResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $seq_id;
    public $pending_requests_total;
    /**
     * @var Model\Inbox
     */
    public $inbox;
}

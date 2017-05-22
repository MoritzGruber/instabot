<?php

namespace InstagramAPI\Response;

class V2InboxResponse extends \InstagramAPI\Response
{
    public $pending_requests_total;
    /**
     * @var string
     */
    public $seq_id;
    /**
     * @var Model\User[]
     */
    public $pending_requests_users;
    /**
     * @var Model\Inbox
     */
    public $inbox;
    /**
     * @var Model\Subscription
     */
    public $subscription;
}

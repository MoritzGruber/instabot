<?php

namespace InstagramAPI\Response;

class DirectShareInboxResponse extends \InstagramAPI\Response
{
    public $shares;
    /**
     * @var string
     */
    public $max_id;
    public $new_shares;
    public $patches;
    public $last_counted_at;
    public $new_shares_info;
}

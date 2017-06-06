<?php

namespace InstagramAPI\Response\Model;

class FriendshipStatus extends \InstagramAPI\Response
{
    public $following;
    public $followed_by;
    public $incoming_request;
    public $outgoing_request;
    public $is_private;
    public $is_blocking_reel;
    public $is_muting_reel;
    public $blocking;
}

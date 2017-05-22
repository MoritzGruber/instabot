<?php

namespace InstagramAPI\Response\Model;

class BroadcastOwner extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $pk;
    /**
     * @var FriendshipStatus
     */
    public $friendship_status;
    public $full_name;
    public $is_verified;
    public $profile_pic_url;
    /**
     * @var string
     */
    public $profile_pic_id;
    public $is_private;
    public $username;
}

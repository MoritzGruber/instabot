<?php

namespace InstagramAPI\Response;

class MediaLikersResponse extends \InstagramAPI\Response
{
    public $user_count;
    /**
     * @var Model\User[]
     */
    public $users;
}

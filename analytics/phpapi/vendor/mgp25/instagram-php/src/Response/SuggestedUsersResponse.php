<?php

namespace InstagramAPI\Response;

class SuggestedUsersResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\User[]
     */
    public $users;
    public $is_backup;
}

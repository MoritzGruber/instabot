<?php

namespace InstagramAPI\Response;

class AutoCompleteUserListResponse extends \InstagramAPI\Response
{
    public $expires;
    /**
     * @var Model\User[]
     */
    public $users;
}

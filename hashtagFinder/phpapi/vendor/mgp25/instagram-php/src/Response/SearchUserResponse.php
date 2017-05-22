<?php

namespace InstagramAPI\Response;

class SearchUserResponse extends \InstagramAPI\Response
{
    public $has_more;
    public $num_results;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var Model\User[]
     */
    public $users;
}

<?php

namespace InstagramAPI\Response;

class ActivityNewsResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Story[]
     */
    public $new_stories;
    /**
     * @var Model\Story[]
     */
    public $old_stories;
    public $continuation;
    /**
     * @var Model\Story[]
     */
    public $friend_request_stories;
    /**
     * @var Model\Counts
     */
    public $counts;
    /**
     * @var Model\Subscription
     */
    public $subscription;
    public $continuation_token;
}

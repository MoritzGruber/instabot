<?php

namespace InstagramAPI\Response\Model;

class Inbox extends \InstagramAPI\Response
{
    public $unseen_count;
    public $has_older;
    public $oldest_cursor;
    public $unseen_count_ts; // is a timestamp
    /**
     * @var Thread[]
     */
    public $threads;
}

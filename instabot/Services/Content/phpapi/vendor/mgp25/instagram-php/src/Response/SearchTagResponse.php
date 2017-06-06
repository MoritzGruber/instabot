<?php

namespace InstagramAPI\Response;

class SearchTagResponse extends \InstagramAPI\Response
{
    public $has_more;
    /**
     * @var Model\Tag[]
     */
    public $results;
}

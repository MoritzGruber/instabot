<?php

namespace InstagramAPI\Response\Model;

class SuggestedUsers extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $id;
    public $view_all_text;
    public $title;
    public $auto_dvance;
    public $type;
    public $tracking_token;
    public $landing_site_type;
    public $landing_site_title;
    public $upsell_fb_pos;
    /*
     * @var Suggestion[]
     */
    public $suggestions;
}

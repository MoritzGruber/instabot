<?php

namespace InstagramAPI\Response\Model;

class BroadcastItem extends \InstagramAPI\Response
{
    public $organic_tracking_token;
    public $published_time;
    /**
     * @var string
     */
    public $id;
    public $rtmp_playback_url;
    public $cover_frame_url;
    public $broadcast_status;
    /**
     * @var string
     */
    public $media_id;
    public $broadcast_message;
    public $viewer_count;
    public $dash_abr_playback_url;
    public $dash_playback_url;
    /**
     * @var User
     */
    public $broadcast_owner;
}

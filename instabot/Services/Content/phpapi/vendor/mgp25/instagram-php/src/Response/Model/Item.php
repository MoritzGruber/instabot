<?php

namespace InstagramAPI\Response\Model;

class Item extends \InstagramAPI\Response
{
    const PHOTO = 1;
    const VIDEO = 2;
    const ALBUM = 8;

    public $taken_at;
    /**
     * @var string
     */
    public $pk;
    /**
     * @var string
     */
    public $id;
    public $device_timestamp;
    public $media_type;
    public $code;
    public $client_cache_key;
    public $filter_type;
    /**
     * @var Attribution
     */
    public $attribution;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    public $original_width;
    public $original_height;
    public $view_count;
    public $organic_tracking_token;
    public $has_more_comments;
    public $max_num_visible_preview_comments;
    public $preview_comments;
    public $comments_disabled;
    public $reel_mentions;
    public $story_cta;
    public $caption_position;
    public $expiring_at;
    public $is_reel_media;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var CarouselMedia[]
     */
    public $carousel_media;
    /**
     * @var Comment[]
     */
    public $comments;
    public $comment_count;
    /**
     * @var Caption
     */
    public $caption;
    public $caption_is_edited;
    public $photo_of_you;
    /**
     * @var VideoVersions[]
     */
    public $video_versions;
    public $has_audio;
    public $video_duration;
    /**
     * @var User
     */
    public $user;
    /**
     * @var User[]
     */
    public $likers;
    public $like_count;
    /**
     * @var string[]
     */
    public $preview;
    public $has_liked;
    public $explore_context;
    public $explore_source_token;
    /**
     * @var Explore
     */
    public $explore;
    public $impression_token;
    /**
     * @var Usertag
     */
    public $usertags;
    public $media_or_ad;
    /**
     * @var Media
     */
    public $media;
    public $stories;
    public $top_likers;
    /**
     * @var SuggestedUsers
     */
    public $suggested_users;
    public $comment_likes_enabled;
    public $can_viewer_save;
    public $has_viewer_saved;
    /**
     * @var Location
     */
    public $location;
    /**
     * @var float
     */
    public $lat;
    /**
     * @var float
     */
    public $lng;
    /**
     * @var StoryLocation[]
     */
    public $story_locations;
    public $algorithm;

    public function setMediaOrAd(
        $params)
    {
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

    public function getItemUrl()
    {
        return 'https://www.instagram.com/p/'.$this->getCode().'/';
    }
}

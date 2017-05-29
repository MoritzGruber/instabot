<?php

namespace InstagramAPI\Response\Model;

class User extends \InstagramAPI\Response
{
    public $username;
    public $has_anonymous_profile_picture;
    public $is_favorite;
    public $profile_pic_url;
    public $full_name;
    /**
     * @var string
     */
    public $pk;
    public $is_verified;
    public $is_private;
    public $coeff_weight;
    /**
     * @var FriendshipStatus
     */
    public $friendship_status;
    public $hd_profile_pic_versions;
    public $byline;
    public $search_social_context;
    public $unseen_count;
    public $mutual_followers_count;
    public $follower_count;
    public $social_context;
    public $media_count;
    public $following_count;
    public $is_business;
    public $usertags_count;
    public $profile_context;
    public $biography;
    public $geo_media_count;
    public $is_unpublished;
    public $allow_contacts_sync; // login prop
    public $show_feed_biz_conversion_icon; // login prop
    /**
     * @var string
     */
    public $profile_pic_id; // Ranked recipents response prop
    public $auto_expand_chaining; // getUserInfoById prop
    public $can_boost_post; // getUserInfoById prop
    public $is_profile_action_needed; // getUserInfoById prop
    public $has_chaining; // getUserInfoById prop
    public $include_direct_blacklist_status; // getUserInfoById prop
    public $can_see_organic_insights; // getUserInfoById prop
    public $can_convert_to_business; // getUserInfoById prop
    public $convert_from_pages; // getUserInfoById prop
    public $show_business_conversion_icon; // getUserInfoById prop
    public $show_conversion_edit_entry; // getUserInfoById prop
    public $show_insights_terms; // getUserInfoById prop
    public $can_create_sponsor_tags; // getUserInfoById prop
    public $hd_profile_pic_url_info; // getUserInfoById prop
    public $usertag_review_enabled; // getUserInfoById prop
    /**
     * @var string[]
     */
    public $profile_context_mutual_follow_ids; // getUserInfoById prop
    /**
     * @var string[]
     */
    public $profile_context_links_with_user_ids; // getUserInfoById prop
    public $has_biography_translation; // getUserInfoById prop
    public $business_contact_method; // getUserInfoById prop
    public $category; // getUserInfoById prop
    public $direct_messaging; // getUserInfoById prop
    /**
     * @var string
     */
    public $fb_page_call_to_action_id; // getUserInfoById prop
    public $is_call_to_action_enabled; // getUserInfoById prop
    public $public_phone_country_code; // getUserInfoById prop
    public $public_phone_number; // getUserInfoById prop
    public $contact_phone_number; // getUserInfoById prop
    /**
     * @var float
     */
    public $latitude; // getUserInfoById prop
    /**
     * @var float
     */
    public $longitude; // getUserInfoById prop
    public $address_street; // getUserInfoById prop
    public $zip; // getUserInfoById prop
    /**
     * @var string
     */
    public $city_id; // getUserInfoById prop
    public $city_name; // getUserInfoById prop
    public $public_email; // getUserInfoById prop
    public $is_needy; // getUserInfoById prop
    public $external_url; // getUserInfoById prop
    public $external_lynx_url; // getUserInfoById prop
    public $email; // getCurrentUser prop
    public $country_code; // getCurrentUser prop
    public $birthday; // getCurrentUser prop
    public $national_number; // getCurrentUser prop
    public $gender; // getCurrentUser prop
    public $phone_number; // getCurrentUser prop
    public $needs_email_confirm; // getCurrentUser prop
}

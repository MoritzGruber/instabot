<?php

namespace InstagramAPI\Response\Model;

class ThreadItemMedia extends \InstagramAPI\Response
{
    const PHOTO = 1;
    const VIDEO = 2;

    public $media_type;
    /**
     * @var Image_Versions2
     */
    public $image_versions2;
    /**
     * @var VideoVersions[]
     */
    public $video_versions;
    public $original_width;
    public $original_height;
}

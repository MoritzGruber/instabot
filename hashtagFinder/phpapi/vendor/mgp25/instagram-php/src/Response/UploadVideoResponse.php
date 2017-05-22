<?php

namespace InstagramAPI\Response;

class UploadVideoResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var float
     */
    public $configure_delay_ms;
    public $result;
    public $message;
}

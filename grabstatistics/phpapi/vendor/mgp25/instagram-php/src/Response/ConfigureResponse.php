<?php

namespace InstagramAPI\Response;

class ConfigureResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var Model\Item
     */
    public $media;
    /**
     * @var string
     */
    public $client_sidecar_id;
}

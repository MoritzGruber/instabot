<?php

namespace InstagramAPI\Response;

class StickerAssetsResponse extends \InstagramAPI\Response
{
    public $version;
    /**
     * @var Model\StaticStickers[]
     */
    public $static_stickers;
}

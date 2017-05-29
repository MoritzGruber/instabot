<?php

namespace InstagramAPI\Response\Model;

class Location extends \InstagramAPI\Response
{
    public $name;
    /**
     * @var string
     */
    public $external_id_source;
    public $external_source;
    public $address;
    /**
     * @var float
     */
    public $lat;
    /**
     * @var float
     */
    public $lng;
    /**
     * @var string
     */
    public $external_id;
    /**
     * @var string
     */
    public $facebook_places_id;
    public $city;
    /**
     * @var string
     */
    public $pk;
}

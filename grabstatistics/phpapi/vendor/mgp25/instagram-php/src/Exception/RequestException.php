<?php

namespace InstagramAPI\Exception;

/**
 * All server-response API related exceptions must derive from this class.
 */
class RequestException extends \RuntimeException implements InstagramException
{
}

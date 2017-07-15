<?php

namespace InstagramAPI\Exception;

/**
 * All internally generated non-server exceptions must derive from this class.
 */
class InternalException extends \RuntimeException implements InstagramException
{
}

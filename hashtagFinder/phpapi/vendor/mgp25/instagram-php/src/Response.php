<?php

namespace InstagramAPI;

class Response extends AutoPropertyHandler
{
    const STATUS_OK = 'ok';
    const STATUS_FAIL = 'fail';

    public $status;
    public $message;
    public $fullResponse;

    public function __construct()
    {
    }

    public function setStatus(
        $status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setMessage(
        $message)
    {
        $this->message = $message;
    }

    /**
     * Gets the message.
     *
     * @throws \Exception If the message object is of an unsupported type.
     *
     * @return string|null A message string if one exists, otherwise NULL.
     */
    public function getMessage()
    {
        // Instagram's API usually returns a simple error string. But in some
        // cases, they instead return a subarray of individual errors, in case
        // of APIs that can return multiple errors at once.
        //
        // Uncomment this if you want to test multiple error handling:
        // $json = '{"status":"fail","message":{"errors":["Select a valid choice. 0 is not one of the available choices."]}}';
        // $json = '{"status":"fail","message":{"errors":["Select a valid choice. 0 is not one of the available choices.","Another error.","One more error."]}}';
        // $obj = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
        // $this->message = $obj->message;

        if (is_null($this->message) || is_string($this->message)) {
            // Single error string or nothing at all.
            return $this->message;
        } elseif (is_object($this->message)) {
            // Multiple errors in an "errors" subarray.
            $vars = get_object_vars($this->message);
            if (count($vars) == 1 && isset($vars['errors']) && is_array($vars['errors'])) {
                // Add "Multiple Errors" prefix if the response contains more than one.
                // But most of the time, there will only be one error in the array.
                $str = (count($vars['errors']) > 1 ? 'Multiple Errors: ' : '');
                $str .= implode(' AND ', $vars['errors']); // Assumes all errors are strings.
                return $str;
            } else {
                throw new \Exception('Unknown message object. Expected errors subarray but found something else. Please submit a ticket about needing an Instagram-API library update!');
            }
        } else {
            throw new \Exception('Unknown message type. Please submit a ticket about needing an Instagram-API library update!');
        }
    }

    public function setFullResponse(
        $response)
    {
        $this->fullResponse = $response;
    }

    public function getFullResponse()
    {
        return $this->fullResponse;
    }

    public function isOk()
    {
        return $this->getStatus() == self::STATUS_OK;
    }
}

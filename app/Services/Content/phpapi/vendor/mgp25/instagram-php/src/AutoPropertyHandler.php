<?php

namespace InstagramAPI;

/**
 * Automatic object property handler.
 *
 * By deriving from this base object, it will automatically create virtual
 * "getX()", "setX()" and "isX()" functions for all of your object's properties.
 *
 * This class is intended to handle Instagram's server responses, so all of your
 * object properties must be named the same way as Instagram's standardized var
 * format, which is "$some_value". That object property can then be magically
 * accessed via "getSomeValue()", "setSomeValue()" and "isSomeValue()".
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class AutoPropertyHandler
{
    /**
     * __CALL is invoked when attempting to access missing functions.
     *
     * This handler auto-maps setters and getters for object properties.
     *
     * @param string $functionName Name of the method being called.
     * @param array  $arguments    Array of arguments passed to the method.
     *
     * @throws \Exception If the function type or property name is invalid.
     *
     * @return mixed
     *
     * @see http://php.net/manual/en/language.oop5.magic.php
     */
    public function __call(
        $functionName,
        $arguments)
    {
        // Extract the components of the function they tried to call.
        $chunks = self::explodeCamelCase($functionName);
        if ($chunks === false || count($chunks) < 2) {
            throw new \Exception("Unknown function {$functionName}.");
        }

        // Determine the type (such as "get") and the property (ie "is_valid").
        $functionType = array_shift($chunks);
        $propertyName = implode('_', $chunks);

        // Make sure the requested function has a corresponding object property.
        if (!property_exists($this, $propertyName)) {
            throw new \Exception("Unknown function {$functionName}.");
        }

        // Return the kind of response expected by their desired function.
        switch ($functionType) {
        case 'get':
            return $this->{$propertyName};
            break;
        case 'set':
            $this->{$propertyName} = $arguments[0];
            break;
        case 'is':
            return $this->{$propertyName} ? true : false;
            break;
        default:
            // Unknown function type prefix...
            throw new \Exception("Unknown function {$functionName}.");
        }
    }

    /**
     * Explodes a string on camelcase boundaries.
     *
     * Examples:
     * - "getSome0XThing" => "get", "some0", "x", "thing".
     * - "getSome0xThing" => "get", "some0x", "thing".
     *
     * @param string $inputString
     *
     * @return string[]|bool Array with parts if successful, otherwise FALSE.
     */
    public static function explodeCamelCase(
        $inputString)
    {
        // Split the input into chunks on all camelcase boundaries.
        // NOTE: The input must be 2+ characters AND have at least one uppercase.
        $chunks = preg_split('/(?=[A-Z])/', $inputString, -1, PREG_SPLIT_NO_EMPTY);
        if ($chunks === false) {
            return false;
        }

        // Process all individual chunks and make them all completely lowercase.
        // NOTE: Since all chunks are split on camelcase boundaries above, it
        // means that each chunk ONLY holds a SINGLE fragment which can ONLY
        // contain at most a SINGLE capital letter (the chunk's first letter).
        foreach ($chunks as &$chunk) {
            $chunk = lcfirst($chunk); // Only first letter may be uppercase.
        }

        return $chunks;
    }
}

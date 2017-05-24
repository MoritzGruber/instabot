<?php

namespace InstagramAPI\Exception;

/**
 * Parses Instagram's API error messages and throws an appropriate exception.
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class ServerMessageThrower
{
    /**
     * Map from server messages to various exceptions.
     *
     * If the first letter of a pattern is "/", we treat it as a regex.
     *
     * The exceptions should be roughly arranged by how common they are, with
     * the most common ones checked first, at the top.
     *
     * Note that not all exceptions are listed below. Some are thrown via other
     * methods than this automatic message parser.
     *
     * WARNING TO CONTRIBUTORS: Do not "contribute" a bunch of function-specific
     * garbage exceptions here, such as "User not found", "No permission to view
     * profile" or other garbage. Those messages are human-readable, unreliable
     * and are also totally non-critical. You should handle them yourself in
     * your end-user applications by simply catching their EndpointException and
     * looking at the contents of its getMessage() property. The exceptions
     * listed below are *critical* exceptions related to the CORE of the API!
     *
     * @var array
     */
    const EXCEPTION_MAP = [
        'LoginRequiredException'       => ['login_required'],
        'CheckpointRequiredException'  => ['checkpoint_required'],
        'FeedbackRequiredException'    => ['feedback_required'],
        'IncorrectPasswordException'   => [
            // "The password you entered is incorrect".
            '/password(.*)incorrect/',
        ],
        'AccountDisabledException'     => [
            // "Your account has been disabled for violating our terms"
            '/account(.*)disabled(.*)violating/',
        ],
        'SentryBlockException'         => ['sentry_block'],
        'InvalidUserException'         => ['invalid_user'],
        'ForcedPasswordResetException' => ['/reset(.*)password/'],
    ];

    /**
     * Parses a server message and throws the appropriate exception.
     *
     * Uses the generic EndpointException if no other exceptions match.
     *
     * @param string|null $prefixString  What prefix to use for the message in
     *                                   the final exception. Should be something
     *                                   helpful such as the name of the class or
     *                                   function which threw. Can be NULL.
     * @param string      $serverMessage The failure string from Instagram's API.
     *
     * @throws InstagramException The appropriate exception.
     */
    public static function autoThrow(
        $prefixString,
        $serverMessage)
    {
        // Some Instagram messages already have punctuation, and others need it.
        $serverMessage = self::prettifyMessage($serverMessage);

        // Now search for the server message in our CRITICAL exception table.
        foreach (self::EXCEPTION_MAP as $exceptionClass => $patterns) {
            foreach ($patterns as $pattern) {
                if ($pattern[0] == '/') {
                    // Regex check.
                    if (preg_match($pattern, $serverMessage)) {
                        return self::_throw($exceptionClass, $prefixString, $serverMessage);
                    }
                } else {
                    // Regular string search.
                    if (strpos($serverMessage, $pattern) !== false) {
                        return self::_throw($exceptionClass, $prefixString, $serverMessage);
                    }
                }
            }
        }

        // No critical exception found. Use a generic "API function exception".
        throw new EndpointException($serverMessage);
    }

    /**
     * Internal function which performs the actual throwing.
     *
     * @param string      $exceptionClass
     * @param string|null $prefixString
     * @param string      $serverMessage
     */
    private static function _throw(
        $exceptionClass,
        $prefixString,
        $serverMessage)
    {
        // We need to specify the full namespace path to the class.
        $fullClassPath = '\\'.__NAMESPACE__.'\\'.$exceptionClass;

        throw new $fullClassPath(
            $prefixString !== null
            ? $prefixString.': '.$serverMessage
            : $serverMessage
        );
    }

    /**
     * Nicely reformats externally generated exception messages.
     *
     * This is used for guaranteeing consistent message formatting with full
     * English sentences, ready for display to the user.
     *
     * @param string $message The original message.
     *
     * @return string The cleaned-up message.
     */
    public static function prettifyMessage(
        $message)
    {
        // Some messages already have punctuation, and others need it. Prettify
        // the message by ensuring that it ALWAYS ends in punctuation, for
        // consistency with all of our internal error messages.
        $lastChar = substr($message, -1);
        if ($lastChar !== '' && $lastChar !== '.' && $lastChar !== '!' && $lastChar !== '?') {
            $message .= '.';
        }

        return $message;
    }
}

<?php

namespace InstagramAPI;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use InstagramAPI\ClientMiddleware;
use InstagramAPI\Exception\ServerMessageThrower;

/**
 * This class handles core API network communication and file uploads.
 *
 * WARNING TO CONTRIBUTORS: Do NOT build ANY monolithic multi-step functions
 * within this class! Every function here MUST be a tiny, individual unit of
 * work, such as "request upload URL" or "upload data to a URL". NOT "request
 * upload URL, upload data, configure its location, post it to a timeline, call
 * your grandmother and make some tea". Because that would be unmaintainable and
 * would lock us into unmodifiable, bloated behaviors!
 *
 * Such larger multi-step algorithms MUST be implemented in Instagram.php
 * instead, and MUST simply use individual functions from this class to
 * accomplish their larger jobs.
 *
 * Thank you, for not writing spaghetti code! ;-)
 *
 * @author mgp25: Founder, Reversing, Project Leader (https://github.com/mgp25)
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class Client
{
    /**
     * The Instagram class instance we belong to.
     *
     * @var \InstagramAPI\Instagram
     */
    protected $_parent;

    /**
     * What user agent to identify our client as.
     *
     * @var string
     */
    protected $_userAgent;

    /**
     * The SSL certificate verification behavior of requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @var bool|string
     */
    protected $_verifySSL;

    /**
     * Proxy to use for all requests. Optional.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @var string|array|null
     */
    protected $_proxy;

    /**
     * Network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see CURLOPT_INTERFACE (http://php.net/curl_setopt)
     *
     * @var string|null
     */
    protected $_outputInterface;

    /**
     * @var \GuzzleHttp\Client
     */
    private $_guzzleClient;

    /**
     * @var \InstagramAPI\ClientMiddleware
     */
    private $_clientMiddleware;

    /**
     * @var \GuzzleHttp\Cookie\FileCookieJar|\GuzzleHttp\Cookie\CookieJar
     */
    private $_cookieJar;

    /**
     * The cookie format expected by the current settings storage.
     *
     * @var string
     */
    private $_settingsCookieFormat;

    /**
     * Constructor.
     *
     * @param \InstagramAPI\Instagram $parent
     */
    public function __construct(
        $parent)
    {
        $this->_parent = $parent;

        // Defaults.
        $this->_verifySSL = true;
        $this->_proxy = null;

        // Create a default handler stack with Guzzle's auto-selected "best
        // possible transfer handler for the user's system", and with all of
        // Guzzle's default middleware (cookie jar support, etc).
        $stack = HandlerStack::create();

        // Create our custom Guzzle client middleware and add it to the stack.
        $this->_clientMiddleware = new ClientMiddleware();
        $stack->push($this->_clientMiddleware);

        // Default request options (immutable after client creation).
        $this->_guzzleClient = new GuzzleClient([
            'handler'         => $stack, // Our middleware is now injected.
            'allow_redirects' => [
                'max' => 8, // Allow up to eight redirects (that's plenty).
            ],
            'connect_timeout' => 30.0, // Give up trying to connect after 30s.
            'decode_content'  => true, // Decode gzip/deflate/etc HTTP responses.
            'timeout'         => 240.0, // Maximum per-request time (seconds).
            // Tells Guzzle to stop throwing exceptions on non-"2xx" HTTP codes,
            // thus ensuring that it only triggers exceptions on socket errors!
            // We'll instead MANUALLY be throwing on certain other HTTP codes.
            'http_errors'     => false,
        ]);
    }

    /**
     * Resets certain Client settings via the current Settings storage.
     *
     * Used whenever the user switches setUser(), to configure our internal state.
     *
     * @param bool $resetCookieJar (optional) Whether to clear current cookies.
     */
    public function updateFromCurrentSettings(
        $resetCookieJar = false)
    {
        $this->_userAgent = $this->_parent->device->getUserAgent();
        $this->_cookieJar = null; // Mark old jar for garbage collection.
        $this->loadCookieJar($resetCookieJar);
    }

    /**
     * Loads all cookies via the current Settings storage.
     *
     * @param bool $resetCookieJar (optional) Whether to clear current cookies.
     */
    public function loadCookieJar(
        $resetCookieJar = false)
    {
        // Get all cookies for the currently active user.
        $userCookies = $this->_parent->settings->getCookies();
        $this->_settingsCookieFormat = $userCookies['format'];

        if ($userCookies['format'] == 'cookiefile') {
            $cookieFilePath = $userCookies['data'];

            // Delete existing cookie jar file if this is a reset.
            if ($resetCookieJar && !empty($cookieFilePath) && is_file($cookieFilePath)) {
                @unlink($cookieFilePath);
            }

            // File-based cookie jar, which also persists temporary session cookies.
            // The FileCookieJar saves to disk whenever its object is destroyed,
            // such as at the end of script or when calling updateFromCurrentSettings().
            $this->_cookieJar = new FileCookieJar($cookieFilePath, true);
        } else {
            // Delete existing cookie data from the storage if this is a reset.
            if ($resetCookieJar) {
                $userCookies['data'] = '';
                $this->_parent->settings->setCookies('');
            }

            // Attempt to restore cookies, otherwise create a new, empty jar.
            $restoredCookies = @json_decode($userCookies['data'], true);
            if (!is_array($restoredCookies)) {
                $restoredCookies = [];
            }

            // Memory-based cookie jar which must be manually saved later.
            $this->_cookieJar = new CookieJar(false, $restoredCookies);
        }

        // Verify that the jar contains a non-expired csrftoken for the API
        // domain. Instagram gives us a 1-year csrftoken whenever we log in.
        // If it's missing, we're definitely NOT logged in! But even if all of
        // these checks succeed, the cookie may still not be valid. It's just a
        // preliminary check to detect definitely-invalid session cookies!
        $foundCSRFToken = false;
        foreach ($this->_cookieJar->getIterator() as $cookie) {
            if ($cookie->getName() == 'csrftoken'
                && $cookie->getDomain() == 'i.instagram.com'
                && $cookie->getExpires() > time()) {
                $foundCSRFToken = true;
                break;
            }
        }
        if (!$foundCSRFToken) {
            $this->_parent->isLoggedIn = false;
        }
    }

    /**
     * Gives you all cookies in the Jar encoded as a JSON string.
     *
     * This allows custom Settings storages to retrieve all cookies for saving.
     *
     * @throws \InvalidArgumentException If the JSON cannot be encoded.
     *
     * @return string
     */
    public function getCookieJarAsJSON()
    {
        if (!$this->_cookieJar instanceof CookieJar) {
            return '[]';
        }

        // Gets ALL cookies from the jar, even temporary session-based cookies.
        $cookies = $this->_cookieJar->toArray();

        // Throws if data can't be encoded as JSON (will never happen).
        $jsonStr = \GuzzleHttp\json_encode($cookies);

        return $jsonStr;
    }

    /**
     * Tells current settings storage to store cookies if necessary.
     *
     * There is no need to call this function manually. It's automatically
     * called by _guzzleRequest()!
     */
    public function saveCookieJar()
    {
        // If it's a FileCookieJar, we don't have to do anything. They are saved
        // automatically to disk when that object is destroyed/garbage collected.
        if ($this->_cookieJar instanceof FileCookieJar) {
            return;
        }

        // Tell any non-file settings storages to persist the latest cookies.
        if ($this->_settingsCookieFormat != 'cookiefile') {
            $newCookies = $this->getCookieJarAsJSON();
            $this->_parent->settings->setCookies($newCookies);
        }
    }

    /**
     * Controls the SSL verification behavior of the Client.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @param bool|string $state TRUE to verify using PHP's default CA bundle,
     *                           FALSE to disable SSL verification (this is
     *                           insecure!), String to verify using this path to
     *                           a custom CA bundle file.
     */
    public function setVerifySSL(
        $state)
    {
        $this->_verifySSL = $state;
    }

    /**
     * Gets the current SSL verification behavior of the Client.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->_verifySSL;
    }

    /**
     * Set the proxy to use for requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @param string|array|null $value String or Array specifying a proxy in
     *                                 Guzzle format, or NULL to disable proxying.
     */
    public function setProxy(
        $value)
    {
        $this->_proxy = $value;
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * Sets the network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see CURLOPT_INTERFACE (http://php.net/curl_setopt)
     *
     * @var string|null Interface name, IP address or hostname, or NULL to
     *                  disable override and let Guzzle use any interface.
     */
    public function setOutputInterface(
        $value)
    {
        $this->_outputInterface = $value;
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->_outputInterface;
    }

    /**
     * Output debugging information.
     *
     * @param string      $method        "GET" or "POST".
     * @param string      $url           The URL or endpoint used for the request.
     * @param string|null $uploadedBody  What was sent to the server. Use NULL to
     *                                   avoid displaying it.
     * @param int|null    $uploadedBytes How many bytes were uploaded. Use NULL to
     *                                   avoid displaying it.
     * @param object      $response      The Guzzle response object from the request.
     * @param string      $responseBody  The actual text-body reply from the server.
     */
    protected function _printDebug(
        $method,
        $url,
        $uploadedBody,
        $uploadedBytes,
        $response,
        $responseBody)
    {
        Debug::printRequest($method, $url);

        // Display the data body that was uploaded, if provided for debugging.
        // NOTE: Only provide this from functions that submit meaningful BODY data!
        if (is_string($uploadedBody)) {
            Debug::printPostData($uploadedBody);
        }

        // Display the number of bytes uploaded in the data body, if provided for debugging.
        // NOTE: Only provide this from functions that actually upload files!
        if (!is_null($uploadedBytes)) {
            Debug::printUpload(Utils::formatBytes($uploadedBytes));
        }

        // Display the number of bytes received from the response, and status code.
        if ($response->hasHeader('x-encoded-content-length')) {
            $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
        } else {
            $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
        }
        Debug::printHttpCode($response->getStatusCode(), $bytes);

        // Display the actual API response body.
        Debug::printResponse($responseBody, $this->_parent->truncatedDebug);
    }

    /**
     * Helper which throws an error if not logged in.
     *
     * Remember to ALWAYS call this function at the top of any API request that
     * requires the user to be logged in!
     *
     * @throws \InstagramAPI\Exception\LoginRequiredException
     */
    protected function _throwIfNotLoggedIn()
    {
        // Check the cached login state. May not reflect what will happen on the
        // server. But it's the best we can check without trying the actual request!
        if (!$this->_parent->isLoggedIn) {
            throw new \InstagramAPI\Exception\LoginRequiredException('User not logged in. Please call login() and then try again.');
        }
    }

    /**
     * Converts a server response to a specific kind of result object.
     *
     * @param mixed $baseClass    An instance of a class object whose properties
     *                            you want to fill from the $response.
     * @param mixed $response     A decoded JSON response from Instagram's server.
     * @param bool  $checkOk      Whether to throw exceptions if the server's
     *                            response wasn't marked as OK by Instagram.
     * @param mixed $fullResponse The raw response object to provide in the
     *                            "getFullResponse()" property. Set this to
     *                            NULL to automatically use $response. That's
     *                            almost always what you want to do!
     *
     * @throws \InstagramAPI\Exception\InstagramException In case of invalid or
     *                                                    failed API response.
     *
     * @return mixed
     */
    public function getMappedResponseObject(
        $baseClass,
        $response,
        $checkOk = true,
        $fullResponse = null)
    {
        if (is_null($response)) {
            throw new \InstagramAPI\Exception\EmptyResponseException('No response from server. Either a connection or configuration error.');
        }

        // Perform mapping.
        $mapper = new \JsonMapper();
        $mapper->bStrictNullTypes = false;
        if ($this->_parent->apiDeveloperDebug) {
            // API developer debugging? Throws error if class lacks properties.
            $mapper->bExceptionOnUndefinedProperty = true;
        }
        $responseObject = $mapper->map($response, $baseClass);

        // Check if the API response was successful.
        if ($checkOk && !$responseObject->isOk()) {
            ServerMessageThrower::autoThrow(get_class($baseClass), $responseObject->getMessage());
        }

        // Save the raw response object as the "getFullResponse()" value.
        if (is_null($fullResponse)) {
            $fullResponse = $response;
        }
        $responseObject->setFullResponse($fullResponse);

        return $responseObject;
    }

    /**
     * Helper which builds in the most important Guzzle options.
     *
     * Takes care of adding all critical options that we need on every request.
     * Such as cookies and the user's proxy. But don't call this function
     * manually. It's automatically called by _guzzleRequest()!
     *
     * @param array $guzzleOptions The options specific to the current request.
     *
     * @return array A guzzle options array.
     */
    protected function _buildGuzzleOptions(
        array $guzzleOptions)
    {
        $criticalOptions = [
            'cookies' => ($this->_cookieJar instanceof CookieJar ? $this->_cookieJar : false),
            'verify'  => $this->_verifySSL,
            'proxy'   => (!is_null($this->_proxy) ? $this->_proxy : null),
        ];

        // Critical options always overwrite identical keys in regular opts.
        // This ensures that we can't screw up the proxy/verify/cookies.
        $finalOptions = array_merge($guzzleOptions, $criticalOptions);

        // Now merge any specific Guzzle cURL-backend overrides. We must do this
        // separately since it's in an associative array and we can't just
        // overwrite that whole array in case the caller had curl options.
        if (!array_key_exists('curl', $finalOptions)) {
            $finalOptions['curl'] = [];
        }

        // Add their network interface override if they want it.
        // This option MUST be non-empty if set, otherwise it breaks cURL.
        if (is_string($this->_outputInterface) && $this->_outputInterface !== '') {
            $finalOptions['curl'][CURLOPT_INTERFACE] = $this->_outputInterface;
        }

        return $finalOptions;
    }

    /**
     * Wraps Guzzle's request and adds special error handling and options.
     *
     * Automatically throws exceptions on certain very serious HTTP errors. And
     * re-wraps all Guzzle errors to our own internal exceptions instead. You
     * must ALWAYS use this (or _apiRequest()) instead of the raw Guzzle Client!
     * However, you can never assume the server response contains what you
     * wanted. Be sure to validate the API reply too, since Instagram's API
     * calls themselves may fail with a JSON message explaining what went wrong.
     *
     * WARNING: This is a semi-lowlevel handler which only applies critical
     * options and HTTP connection handling! Most functions will want to call
     * _apiRequest() instead. An even higher-level handler which takes care of
     * debugging, server response checking and response decoding!
     *
     * @param string $method        HTTP method.
     * @param string $uri           Full URI string.
     * @param array  $guzzleOptions Request options to apply.
     *
     * @throws \InstagramAPI\Exception\NetworkException   For any network/socket related errors.
     * @throws \InstagramAPI\Exception\ThrottledException When we're throttled by server.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _guzzleRequest(
        $method,
        $uri,
        array $guzzleOptions = [])
    {
        // Add critically important options for authenticating the request.
        $guzzleOptions = $this->_buildGuzzleOptions($guzzleOptions);

        // Attempt the request. Will throw in case of socket errors!
        try {
            $response = $this->_guzzleClient->request($method, $uri, $guzzleOptions);
        } catch (\Exception $e) {
            // Re-wrap Guzzle's exception using our own NetworkException.
            throw new \InstagramAPI\Exception\NetworkException($e);
        }

        // Detect very serious HTTP status codes in the response.
        $httpCode = $response->getStatusCode();
        switch ($httpCode) {
        case 429: // "429 Too Many Requests"
            throw new \InstagramAPI\Exception\ThrottledException('Throttled by Instagram because of too many API requests.');
            break;
        // NOTE: Detecting "404" errors was intended to help us detect when API
        // endpoints change. But it turns out that A) Instagram uses "valid" 404
        // status codes in actual API replies to indicate "user not found" and
        // similar states for various lookup functions. So we can't die on 404,
        // since "404" API calls actually succeeded in most cases. And B) Their
        // API doesn't 404 if you try an invalid endpoint URL. Instead, it just
        // redirects you to their official homepage. So catching 404 is both
        // pointless and harmful. This is a warning to future contributors!
        // ---
        // case 404: // "404 Not Found"
        //     die("The requested URL was not found (\"{$uri}\").");
        //     break;
        }

        // Save the new, most up-to-date cookies.
        $this->saveCookieJar();

        // The response may still have serious but "valid response" errors, such
        // as "400 Bad Request". But it's up to the CALLER to handle those!
        return $response;
    }

    /**
     * Internal wrapper around _guzzleRequest().
     *
     * This takes care of many common additional tasks needed by our library,
     * so you should try to always use this instead of the raw _guzzleRequest()!
     *
     * Available library options are:
     * - 'noDebug': Can be set to TRUE to forcibly hide debugging output for
     *   this request. The user controls debugging globally, but this is an
     *   override that prevents them from seeing certain requests that you may
     *   not want to trigger debugging (such as perhaps individual steps of a
     *   file upload process). However, debugging SHOULD be allowed in MOST cases!
     *   So only use this feature if you have a very good reason.
     * - 'debugUploadedBody': Set to TRUE to make debugging display the data that
     *   was uploaded in the body of the request. DO NOT use this if your function
     *   uploaded binary data, since printing those bytes would kill the terminal!
     * - 'debugUploadedBytes': Set to TRUE to make debugging display the size of
     *   the uploaded body data. Should ALWAYS be TRUE when uploading binary data.
     * - 'decodeToObject': If this option is provided, it MUST either be an instance
     *   of a new class object, or FALSE to signify that you don't want us to do any
     *   object decoding. Omitting this option entirely is the same as FALSE, but
     *   it is highly recommended to ALWAYS include this option (even if FALSE),
     *   for code clarity about what you intend to do with this function's response!
     *
     * @param string $method         HTTP method ("GET" or "POST").
     * @param string $endpoint       Relative API endpoint, such as "upload/photo/",
     *                               but can also be a full URI starting with "http:"
     *                               or "https:", which is then used as-provided.
     * @param array  $guzzleOptions  Guzzle request() options to apply to the HTTP request.
     * @param array  $libraryOptions Additional options for controlling Library features
     *                               such as the debugging output and response decoding.
     *
     * @throws \InstagramAPI\Exception\NetworkException   For any network/socket related errors.
     * @throws \InstagramAPI\Exception\ThrottledException When we're throttled by server.
     * @throws \InstagramAPI\Exception\InstagramException When "decodeToObject"
     *                                                    was requested and the
     *                                                    API response was
     *                                                    invalid or failed or
     *                                                    class decode failed.
     * @throws \InvalidArgumentException                  If no object provided.
     *
     * @return array An array with the Guzzle "response" object, and the raw
     *               non-decoded HTTP "body" of the request, and the "object" if
     *               the "decodeToObject" library option was used.
     */
    protected function _apiRequest(
        $method,
        $endpoint,
        array $guzzleOptions = [],
        array $libraryOptions = [])
    {
        // Determine the URI to use (it's either relative to API, or a full URI).
        if (strncmp($endpoint, 'http:', 5) === 0 || strncmp($endpoint, 'https:', 6) === 0) {
            $uri = $endpoint;
        } else {
            $uri = Constants::API_URL.$endpoint;
        }

        // Perform the API request and retrieve the raw HTTP response body.
        $guzzleResponse = $this->_guzzleRequest($method, $uri, $guzzleOptions);
        $body = $guzzleResponse->getBody()->getContents();

        // Debugging (must be shown before possible decoding error).
        if ($this->_parent->debug && (!isset($libraryOptions['noDebug']) || !$libraryOptions['noDebug'])) {
            // Determine whether we should display the contents of the UPLOADED body.
            if (isset($libraryOptions['debugUploadedBody']) && $libraryOptions['debugUploadedBody']) {
                $uploadedBody = isset($guzzleOptions['body']) ? $guzzleOptions['body'] : null;
            } else {
                $uploadedBody = null; // Don't display.
            }

            // Determine whether we should display the size of the UPLOADED body.
            if (isset($libraryOptions['debugUploadedBytes']) && $libraryOptions['debugUploadedBytes']) {
                // Calculate the uploaded bytes by looking at request's body size, if it exists.
                $uploadedBytes = isset($guzzleOptions['body']) ? strlen($guzzleOptions['body']) : null;
            } else {
                $uploadedBytes = null; // Don't display.
            }

            $this->_printDebug($method, $endpoint, $uploadedBody, $uploadedBytes, $guzzleResponse, $body);
        }

        // Begin building the result array.
        $result = [
            'response' => $guzzleResponse,
            'body'     => $body,
        ];

        // Perform optional API response decoding and success validation.
        if (isset($libraryOptions['decodeToObject']) && $libraryOptions['decodeToObject'] !== false) {
            if (!is_object($libraryOptions['decodeToObject'])) {
                throw new \InvalidArgumentException('Object decoding requested, but no object instance provided.');
            }

            // Check for API response success and attempt to decode it to the desired class.
            $result['object'] = $this->getMappedResponseObject(
                $libraryOptions['decodeToObject'],
                self::api_body_decode($body), // Important: Special JSON decoder.
                true // Forcibly validates that the API response "status" MUST be Ok.
            );
        }

        return $result;
    }

    /**
     * Perform an Instagram API call.
     *
     * @param string     $endpoint  The relative API endpoint URL to call.
     * @param array|null $postData  Optional array of POST-parameters, to do a
     *                              POST request instead of a GET.
     * @param bool       $needsAuth Whether this API call needs authorization.
     * @param bool       $assoc     Whether to decode to associative array,
     *                              otherwise we decode to object.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return mixed An object or associative array.
     */
    public function api(
        $endpoint,
        $postData = null,
        $needsAuth = false,
        $assoc = true)
    {
        if (!$needsAuth) { // Only allow non-authenticated requests until logged in.
            $this->_throwIfNotLoggedIn();
        }

        // Build request options.
        $headers = [
            'User-Agent'            => $this->_userAgent,
            // Keep the API's HTTPS connection alive in Guzzle for future
            // re-use, to greatly speed up all further queries after this.
            'Connection'            => 'keep-alive',
            'Accept'                => '*/*',
            'Accept-Encoding'       => Constants::ACCEPT_ENCODING,
            'X-IG-Capabilities'     => Constants::X_IG_Capabilities,
            'X-IG-Connection-Type'  => Constants::X_IG_Connection_Type,
            'X-IG-Connection-Speed' => mt_rand(1000, 3700).'kbps',
            'X-FB-HTTP-Engine'      => Constants::X_FB_HTTP_Engine,
            'Content-Type'          => Constants::CONTENT_TYPE,
            'Accept-Language'       => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
        ];
        $method = 'GET';
        if ($postData) {
            $method = 'POST';
            $options['body'] = $postData;
        }

        // Perform the API request.
        $response = $this->_apiRequest(
            $method,
            $endpoint,
            $options,
            [
                'debugUploadedBody'  => true,
                'debugUploadedBytes' => false,
                'decodeToObject'     => false,
            ]
        );

        // Process cookies to extract the latest token.
        $csrftoken = null;
        $cookies = $this->_cookieJar->getIterator();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == 'csrftoken') {
                $csrftoken = $cookie->getValue();
                break;
            }
        }

        // Manually decode the JSON response, since we didn't request object decoding
        // above. This lets our caller later map it to any object they want (or none).
        $result = self::api_body_decode($response['body'], $assoc);

        return [$csrftoken, $result];
    }

    /**
     * Uploads a photo to Instagram.
     *
     * @param string $targetFeed    Target feed for this media ("timeline", "story" or "album").
     * @param string $photoFilename The photo filename.
     * @param string $fileType      Whether the file is a "photofile" or "videofile".
     *                              In case of videofile we'll generate a thumbnail from it.
     * @param null   $uploadId      Custom upload ID if wanted. Otherwise autogenerated.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UploadPhotoResponse
     */
    public function uploadPhotoData(
        $targetFeed,
        $photoFilename,
        $fileType = 'photofile',
        $uploadId = null)
    {
        $this->_throwIfNotLoggedIn();

        $endpoint = 'upload/photo/';

        // Verify that the file exists locally.
        if (!is_file($photoFilename)) {
            throw new \InvalidArgumentException(sprintf('The photo file "%s" does not exist on disk.', $photoFilename));
        }

        // Determine which file contents to upload.
        if ($fileType == 'videofile') {
            // Generate a thumbnail from a video file.
            $photoData = Utils::createVideoIcon($photoFilename);
        } else {
            $photoData = file_get_contents($photoFilename);
        }

        // Generate an upload ID if none was provided.
        if (is_null($uploadId)) {
            $uploadId = Utils::generateUploadId();
        }

        // Prepare payload for the upload request.
        $boundary = $this->_parent->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $uploadId,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $boundary,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->_parent->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'image_compression',
                'data' => '{"lib_name":"jt","lib_version":"1.3.0","quality":"87"}',
            ],
            [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => $photoData,
                'filename' => 'pending_media_'.Utils::generateUploadId().'.jpg',
                'headers'  => [
                    'Content-Transfer-Encoding: binary',
                    'Content-Type: application/octet-stream',
                ],
            ],
        ];
        if ($targetFeed == 'album') {
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'is_sidecar',
                'data' => '1',
            ];
            if ($fileType == 'videofile') {
                $bodies[] = [
                    'type' => 'form-data',
                    'name' => 'media_type',
                    'data' => '2',
                ];
            }
        }
        $payload = $this->_buildBody($bodies, $boundary);

        // Build the request options.
        $method = 'POST';
        $headers = [
            'User-Agent'            => $this->_userAgent,
            'Connection'            => 'keep-alive',
            'Accept'                => '*/*',
            'Accept-Encoding'       => Constants::ACCEPT_ENCODING,
            'X-IG-Capabilities'     => Constants::X_IG_Capabilities,
            'X-IG-Connection-Type'  => Constants::X_IG_Connection_Type,
            'X-IG-Connection-Speed' => mt_rand(1000, 3700).'kbps',
            'X-FB-HTTP-Engine'      => Constants::X_FB_HTTP_Engine,
            'Content-Type'          => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language'       => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the API request.
        $response = $this->_apiRequest(
            $method,
            $endpoint,
            $options,
            [
                'debugUploadedBody'  => false,
                'debugUploadedBytes' => true,
                'decodeToObject'     => new Response\UploadPhotoResponse(),
            ]
        );

        // NOTE: The server's reply includes the upload id that was used,
        // so we don't need to return anything more than their reply.
        // You can get it from the response object->getUploadId().
        return $response['object'];
    }

    /**
     * Asks Instagram for parameters for uploading a new video.
     *
     * @param string $targetFeed       Target feed for this media ("timeline", "story" or "album").
     * @param array  $internalMetadata (optional) Internal library-generated metadata key-value pairs.
     *
     * @throws \InstagramAPI\Exception\InstagramException If the request fails.
     *
     * @return array
     */
    public function requestVideoUploadURL(
        $targetFeed,
        array $internalMetadata = [])
    {
        $this->_throwIfNotLoggedIn();

        $endpoint = 'upload/video/';

        // Critically important internal library-generated metadata parameters:
        // NOTE: NO INTERNAL DATA IS NEEDED HERE YET.

        // Prepare payload for the "pre-upload" request.
        $boundary = $this->_parent->uuid;
        $uploadId = Utils::generateUploadId();
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $uploadId,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->_parent->token,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $boundary,
            ],
        ];
        if ($targetFeed == 'album') {
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'is_sidecar',
                'data' => '1',
            ];
        } else {
            // Get all of the INTERNAL metadata needed for non-album videos.
            /** @var array Video details array. */
            $videoDetails = $internalMetadata['videoDetails'];

            $bodies[] = [
                'type' => 'form-data',
                'name' => 'media_type',
                'data' => '2',
            ];
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'upload_media_duration_ms',
                // NOTE: ceil() is to round up and get rid of any MS decimals.
                'data' => (int) ceil($videoDetails['duration'] * 1000),
            ];
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'upload_media_width',
                'data' => $videoDetails['width'],
            ];
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'upload_media_height',
                'data' => $videoDetails['height'],
            ];
        }
        $payload = $this->_buildBody($bodies, $boundary);

        // Build the "pre-upload" request's options.
        $method = 'POST';
        $headers = [
            'User-Agent'      => $this->_userAgent,
            'Connection'      => 'keep-alive',
            'Accept'          => '*/*',
            'Content-Type'    => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language' => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the "pre-upload" API request.
        $response = $this->_apiRequest(
            $method,
            $endpoint,
            $options,
            [
                'debugUploadedBody'  => true,
                'debugUploadedBytes' => false,
                'decodeToObject'     => new Response\UploadJobVideoResponse(),
            ]
        );

        // Determine where their API wants us to upload the video file.
        return [
            'uploadId'  => $uploadId,
            'uploadUrl' => $response['object']->getVideoUploadUrls()[3]->url,
            'job'       => $response['object']->getVideoUploadUrls()[3]->job,
        ];
    }

    /**
     * Performs a chunked upload of a video file.
     *
     * Note that video uploads often fail when their server is overloaded.
     * So you may have to redo this call multiple times.
     *
     * @param string $targetFeed    Target feed for this media ("timeline", "story" or "album").
     * @param string $videoFilename The video filename.
     * @param array  $uploadParams  An array created by requestVideoUploadURL()!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the upload fails.
     *
     * @return \InstagramAPI\Response\UploadVideoResponse
     */
    public function uploadVideoChunks(
        $targetFeed,
        $videoFilename,
        array $uploadParams)
    {
        $this->_throwIfNotLoggedIn();

        // Verify that the file exists locally.
        if (!is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        // To support video uploads to albums, we MUST fake-inject the
        // "sessionid" cookie from "i.instagram" into our "upload.instagram"
        // request, otherwise the server will reply with a "StagedUpload not
        // found" error when the final chunk has been uploaded.
        $sessionIDCookie = null;
        if ($targetFeed == 'album') {
            foreach ($this->_cookieJar->getIterator() as $cookie) {
                if ($cookie->getName() == 'sessionid'
                    && $cookie->getDomain() == 'i.instagram.com') {
                    $sessionIDCookie = $cookie->getValue();
                    break;
                }
            }
            if ($sessionIDCookie === null) {
                throw new \InstagramAPI\Exception\UploadFailedException('Unable to find the necessary SessionID cookie for uploading video album chunks.');
            }
        }

        // Determine correct file extension for video format.
        $videoExt = pathinfo($videoFilename, PATHINFO_EXTENSION);
        if (strlen($videoExt) == 0) {
            $videoExt = 'mp4'; // Fallback.
        }

        // Video upload must be done in exactly 4 chunks; determine chunk size!
        $numChunks = 4;
        $videoSize = filesize($videoFilename);
        $maxChunkSize = ceil($videoSize / $numChunks);

        // Read and upload each individual chunk.
        $rangeStart = 0;
        $handle = fopen($videoFilename, 'r');
        try {
            for ($chunkIdx = 1; $chunkIdx <= $numChunks; ++$chunkIdx) {
                // Extract the chunk.
                $chunkData = fread($handle, $maxChunkSize);
                $chunkSize = strlen($chunkData);

                // Calculate where the current byte range will end.
                // NOTE: Range is 0-indexed, and Start is the first byte of the
                // new chunk we're uploading, hence we MUST subtract 1 from End.
                // And our FINAL chunk's End must be 1 less than the filesize!
                $rangeEnd = $rangeStart + ($chunkSize - 1);

                // Build the current chunk's request options.
                $method = 'POST';
                $headers = [
                    'User-Agent'          => $this->_userAgent,
                    'Connection'          => 'keep-alive',
                    'Accept'              => '*/*',
                    'Cookie2'             => '$Version=1',
                    'Accept-Encoding'     => 'gzip, deflate',
                    'Content-Type'        => 'application/octet-stream',
                    'Session-ID'          => $uploadParams['uploadId'],
                    'Accept-Language'     => Constants::ACCEPT_LANGUAGE,
                    'Content-Disposition' => "attachment; filename=\"video.{$videoExt}\"",
                    'Content-Range'       => 'bytes '.$rangeStart.'-'.$rangeEnd.'/'.$videoSize,
                    'job'                 => $uploadParams['job'],
                ];
                $options = [
                    'headers' => $headers,
                    'body'    => $chunkData,
                ];

                // When uploading videos to albums, we must fake-inject the
                // "sessionid" cookie (the official app fake-injects it too).
                if ($targetFeed == 'album' && $sessionIDCookie !== null) {
                    // We'll add it with the default options ("single use") so
                    // that the fake cookie is only added to THIS request.
                    $this->_clientMiddleware->addFakeCookie('sessionid', $sessionIDCookie);
                }

                // Perform the upload of the current chunk.
                $response = $this->_apiRequest(
                    $method,
                    $uploadParams['uploadUrl'],
                    $options,
                    [
                        'debugUploadedBody'  => false,
                        'debugUploadedBytes' => true,
                        'decodeToObject'     => false,
                    ]
                );

                // Check if Instagram's server has bugged out.
                // NOTE: On everything except the final chunk, they MUST respond
                // with "0-BYTESTHEYHAVESOFAR/TOTALBYTESTHEYEXPECT". The "0-" is
                // what matters. When they bug out, they drop chunks and the
                // start range on the server-side won't be at zero anymore.
                if ($chunkIdx < $numChunks) {
                    if (strncmp($response['body'], '0-', 2) !== 0) {
                        // Their range doesn't start with "0-". Abort!
                        break; // Don't waste time uploading further chunks!
                    }
                }

                // Update the range's Start for the next iteration.
                // NOTE: It's the End-byte of the previous range, plus one.
                $rangeStart = $rangeEnd + 1;
            }
        } finally {
            // Guaranteed to release handle even if something bad happens above!
            fclose($handle);
        }

        // NOTE: $response below refers to the final chunk's result!

        // Protection against Instagram's upload server being bugged out!
        // NOTE: When their server is bugging out, the final chunk result will
        // just be yet another range specifier such as "328600-657199/657200",
        // instead of a "{...}" JSON object. Because their server will have
        // dropped all earlier chunks when they bug out (due to overload or w/e).
        if (substr($response['body'], 0, 1) !== '{') {
            throw new \InstagramAPI\Exception\UploadFailedException(sprintf("Upload of \"%s\" failed. Instagram's server returned an unexpected reply and is probably overloaded.", $videoFilename));
        }

        // Manually decode the final API response and check for successful chunked upload.
        $upload = $this->getMappedResponseObject(
            new Response\UploadVideoResponse(),
            self::api_body_decode($response['body']), // Important: Special JSON decoder.
            true // Forcibly validates that the API response "status" MUST be Ok.
        );

        return $upload;
    }

    /**
     * Uploads a video to Instagram, with multiple retries.
     *
     * The retries are very important since their media server is often overloaded and
     * aborts the upload. So you almost always want this instead of uploadVideoChunks().
     *
     * @param string $targetFeed    Target feed for this media ("timeline", "story" or "album").
     * @param string $videoFilename The video filename.
     * @param array  $uploadParams  An array created by requestVideoUploadURL()!
     * @param int    $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the upload fails.
     *
     * @return \InstagramAPI\Response\UploadVideoResponse
     */
    public function uploadVideoData(
        $targetFeed,
        $videoFilename,
        array $uploadParams,
        $maxAttempts = 10)
    {
        $this->_throwIfNotLoggedIn();

        // Verify that the file exists locally.
        if (!is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        // Upload the entire video file, with retries in case of chunk upload errors.
        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                // Attempt an upload and return the result if successful.
                return $this->uploadVideoChunks($targetFeed, $videoFilename, $uploadParams);
            } catch (\InstagramAPI\Exception\UploadFailedException $e) {
                if ($attempt < $maxAttempts) {
                    // Do nothing, since we'll be retrying the failed upload...
                } else {
                    // Re-throw unhandled exception.
                    throw $e;
                }
            }
        }
    }

    /**
     * Changes your account's profile picture.
     *
     * @param string $photoFilename The photo filename.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\Model\User
     */
    public function changeProfilePicture(
        $photoFilename)
    {
        $this->_throwIfNotLoggedIn();

        $endpoint = 'accounts/change_profile_picture/';

        // Verify that the file exists locally.
        if (!is_file($photoFilename)) {
            throw new \InvalidArgumentException(sprintf('The photo file "%s" does not exist on disk.', $photoFilename));
        }

        // Prepare payload for the upload request.
        $boundary = $this->_parent->uuid;
        $uData = json_encode([
            '_csrftoken' => $this->_parent->token,
            '_uuid'      => $boundary,
            '_uid'       => $this->_parent->account_id,
        ]);
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'ig_sig_key_version',
                'data' => Constants::SIG_KEY_VERSION,
            ],
            [
                'type' => 'form-data',
                'name' => 'signed_body',
                'data' => hash_hmac('sha256', $uData, Constants::IG_SIG_KEY).$uData,
            ],
            [
                'type'     => 'form-data',
                'name'     => 'profile_pic',
                'data'     => file_get_contents($photoFilename),
                'filename' => 'profile_pic',
                'headers'  => [
                    'Content-Type: application/octet-stream',
                    'Content-Transfer-Encoding: binary',
                ],
            ],
        ];
        $payload = $this->_buildBody($bodies, $boundary);

        // Build the request options.
        $method = 'POST';
        $headers = [
            'User-Agent'       => $this->_userAgent,
            'Proxy-Connection' => 'keep-alive',
            'Connection'       => 'keep-alive',
            'Accept'           => '*/*',
            'Content-Type'     => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language'  => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the API request.
        $response = $this->_apiRequest(
            $method,
            $endpoint,
            $options,
            [
                'debugUploadedBody'  => false,
                'debugUploadedBytes' => true,
                'decodeToObject'     => new Response\Model\User(),
            ]
        );

        return $response['object'];
    }

    /**
     * Perform a direct media share to specific users.
     *
     * @param string          $shareType  Either "share", "message" or "photo".
     * @param string[]|string $recipients Either a single recipient or an array
     *                                    of multiple recipient strings.
     * @param array           $shareData  Depends on shareType: "share" uses
     *                                    "text" and "media_id". "message" uses
     *                                    "text". "photo" uses "text" and "filepath".
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function directShare(
        $shareType,
        $recipients,
        array $shareData)
    {
        $this->_throwIfNotLoggedIn();

        // Determine which endpoint to use and validate input.
        switch ($shareType) {
        case 'share':
            $endpoint = 'direct_v2/threads/broadcast/media_share/?media_type=photo';
            if ((!isset($shareData['text']) || is_null($shareData['text']))
                && (!isset($shareData['media_id']) || is_null($shareData['media_id']))) {
                throw new \InvalidArgumentException('You must provide either a text message or a media id.');
            }
            break;
        case 'message':
            $endpoint = 'direct_v2/threads/broadcast/text/';
            if (!isset($shareData['text']) || is_null($shareData['text'])) {
                throw new \InvalidArgumentException('No text message provided.');
            }
            break;
        case 'photo':
            $endpoint = 'direct_v2/threads/broadcast/upload_photo/';
            if (!isset($shareData['filepath']) || is_null($shareData['filepath'])) {
                throw new \InvalidArgumentException('No photo path provided.');
            }
            break;
        default:
            throw new \InvalidArgumentException('Invalid shareType parameter value.');
        }

        // Build the list of direct-share recipients.
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }
        $recipient_users = '"'.implode('","', $recipients).'"';

        // Prepare payload for the direct-share request.
        // WARNING: EDIT THIS *VERY CAREFULLY* IN THE FUTURE!
        // THE DIRECT-SHARE REQUESTS USE A LOT OF IDENTICAL DATA,
        // SO WE CONSTRUCT THEIR FINAL $bodies STEP BY STEP TO AVOID
        // CODE REPETITION. BUT RECKLESS FUTURE CHANGES BELOW COULD
        // BREAK *ALL* REQUESTS IF YOU ARE NOT *VERY* CAREFUL!!!
        $boundary = $this->_parent->uuid;
        $bodies = [];
        if ($shareType == 'share') {
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $shareData['media_id'],
            ];
        }
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'recipient_users',
            'data' => "[[{$recipient_users}]]",
        ];
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'client_context',
            // WARNING: Must be random every time otherwise we can only
            // make a single post per direct-discussion thread.
            'data' => Signatures::generateUUID(true),
        ];
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'thread_ids',
            'data' => '["0"]',
        ];
        if ($shareType == 'photo') {
            $bodies[] = [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => file_get_contents($shareData['filepath']),
                'filename' => 'photo',
                'headers'  => [
                    'Content-Type: '.mime_content_type($shareData['filepath']),
                    'Content-Transfer-Encoding: binary',
                ],
            ];
        }
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'text',
            'data' => (!isset($shareData['text']) || is_null($shareData['text']) ? '' : $shareData['text']),
        ];
        $payload = $this->_buildBody($bodies, $boundary);

        // Build the request options.
        $method = 'POST';
        $headers = [
            'User-Agent'       => $this->_userAgent,
            'Proxy-Connection' => 'keep-alive',
            'Connection'       => 'keep-alive',
            'Accept'           => '*/*',
            'Content-Type'     => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language'  => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the API request.
        $response = $this->_apiRequest(
            $method,
            $endpoint,
            $options,
            [
                'debugUploadedBody'  => false,
                'debugUploadedBytes' => true,
                'decodeToObject'     => new \InstagramAPI\Response(),
            ]
        );

        return $response['object'];
    }

    /**
     * Internal helper for building a properly formatted request body.
     *
     * @param array  $bodies
     * @param string $boundary
     *
     * @return string
     */
    protected function _buildBody(
        array $bodies,
        $boundary)
    {
        $body = '';
        foreach ($bodies as $b) {
            $body .= '--'.$boundary."\r\n";
            $body .= 'Content-Disposition: '.$b['type'].'; name="'.$b['name'].'"';
            if (isset($b['filename'])) {
                $ext = pathinfo($b['filename'], PATHINFO_EXTENSION);
                $body .= '; filename="'.'pending_media_'.Utils::generateUploadId().'.'.$ext.'"';
            }
            if (isset($b['headers']) && is_array($b['headers'])) {
                foreach ($b['headers'] as $header) {
                    $body .= "\r\n".$header;
                }
            }

            $body .= "\r\n\r\n".$b['data']."\r\n";
        }
        $body .= '--'.$boundary.'--';

        return $body;
    }

    /**
     * Decode a JSON reply from Instagram's API.
     *
     * WARNING: EXTREMELY IMPORTANT! NEVER, *EVER* USE THE BASIC "json_decode"
     * ON API REPLIES! ALWAYS USE THIS METHOD INSTEAD, TO ENSURE PROPER DECODING
     * OF BIG NUMBERS! OTHERWISE YOU'LL TRUNCATE VARIOUS INSTAGRAM API FIELDS!
     *
     * @param string $json  The body (JSON string) of the API response.
     * @param bool   $assoc When TRUE, decode to associative array instead of object.
     *
     * @return object|array|null Object if assoc false, Array if assoc true,
     *                           or NULL if unable to decode JSON.
     */
    public static function api_body_decode(
        $json,
        $assoc = false)
    {
        return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
    }
}

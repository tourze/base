<?php

namespace tourze\Http\Request;

use tourze\Base\Helper\Arr;
use tourze\Http\Http;
use tourze\Http\Request;
use tourze\Http\Response;
use tourze\Http\Request\Exception\ClientRecursionException;

/**
 * 请求的具体实现类，支持两种请求方式，一种是内部请求，一种是外部请求
 *
 * @package tourze\Http\Request
 */
abstract class RequestClient
{

    /**
     * @var  bool  Should redirects be followed?
     */
    protected $_follow = false;

    /**
     * @var  array  Headers to preserve when following a redirect
     */
    protected $_followHeaders = ['Authorization'];

    /**
     * @var  bool  Follow 302 redirect with original request method?
     */
    protected $_strictRedirect = true;

    /**
     * @var array  Callbacks to use when response contains given headers
     */
    protected $_headerCallbacks = [
        'Location' => 'tourze\Http\Request\RequestClient::onHeaderLocation'
    ];

    /**
     * @var int  Maximum number of requests that header callbacks can trigger before the request is aborted
     */
    protected $_maxCallbackDepth = 5;

    /**
     * @var int  Tracks the callback depth of the currently executing request
     */
    protected $_callbackDepth = 1;

    /**
     * @var array  Arbitrary parameters that are shared with header callbacks through their RequestClient object
     */
    protected $_callbackParams = [];

    /**
     * Creates a new `RequestClient` object,
     * allows for dependency injection.
     *
     * @param   array $params Params
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $key => $value)
        {
            if (method_exists($this, $key))
            {
                $this->$key($value);
            }
        }
    }

    /**
     * Processes the request, executing the controller action that handles this
     * request, determined by the [Route].
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     * By default, the output from the controller is captured and returned, and
     * no headers are sent.
     *     $request->execute();
     *
     * @param  Request $request
     * @return Response
     * @throws ClientRecursionException
     * @throws Exception\RequestException
     */
    public function execute(Request $request)
    {
        // Prevent too much recursion of header callback requests
        if ($this->callbackDepth() > $this->maxCallbackDepth())
        {
            throw new ClientRecursionException('Could not execute request to :uri - too many recursions after :depth requests', [
                ':uri'   => $request->uri,
                ':depth' => $this->callbackDepth() - 1,
            ]);
        }

        // Execute the request and pass the currently used protocol
        $origResponse = $response = Response::factory(['_protocol' => $request->protocol]);

        $response = $this->executeRequest($request, $response);

        // Execute response callbacks
        foreach ($this->headerCallbacks() as $header => $callback)
        {
            if ($response->headers($header))
            {
                $cbResult = call_user_func($callback, $request, $response, $this);

                if ($cbResult instanceof Request)
                {
                    // If the callback returns a request, automatically assign client params
                    $this->assignClientProperties($cbResult->client());
                    $cbResult->client()
                        ->callbackDepth($this->callbackDepth() + 1);

                    // Execute the request
                    $response = $cbResult->execute();
                }
                elseif ($cbResult instanceof Response)
                {
                    // Assign the returned response
                    $response = $cbResult;
                }

                // If the callback has created a new response, do not process any further
                if ($response !== $origResponse)
                {
                    break;
                }
            }
        }

        return $response;
    }

    /**
     * Processes the request passed to it and returns the response from
     * the URI resource identified.
     * This method must be implemented by all clients.
     *
     * @param  Request  $request request to execute by client
     * @param  Response $response
     * @return Response
     */
    abstract public function executeRequest(Request $request, Response $response);

    /**
     * Getter and setter for the follow redirects
     * setting.
     *
     * @param   bool $follow Boolean indicating if redirects should be followed
     *
     * @return  bool
     * @return  RequestClient
     */
    public function follow($follow = null)
    {
        if (null === $follow)
        {
            return $this->_follow;
        }

        $this->_follow = $follow;

        return $this;
    }

    /**
     * Getter and setter for the follow redirects
     * headers array.
     *
     * @param   array $followHeaders Array of headers to be re-used when following a Location header
     * @return  array
     * @return  RequestClient
     */
    public function followHeaders($followHeaders = null)
    {
        if (null === $followHeaders)
        {
            return $this->_followHeaders;
        }

        $this->_followHeaders = $followHeaders;

        return $this;
    }

    /**
     * Getter and setter for the strict redirects setting
     * [!!] HTTP/1.1 specifies that a 302 redirect should be followed using the
     * original request method. However, the vast majority of clients and servers
     * get this wrong, with 302 widely used for 'POST - 302 redirect - GET' patterns.
     * By default, client is fully compliant with the HTTP spec. Some
     * non-compliant third party sites may require that strictRedirect is set
     * false to force the client to switch to GET following a 302 response.
     *
     * @param  bool $strictRedirect Boolean indicating if 302 redirects should be followed with the original method
     * @return RequestClient
     */
    public function strictRedirect($strictRedirect = null)
    {
        if (null === $strictRedirect)
        {
            return $this->_strictRedirect;
        }

        $this->_strictRedirect = $strictRedirect;

        return $this;
    }

    /**
     * Getter and setter for the header callbacks array.
     * Accepts an array with HTTP response headers as keys and a PHP callback
     * function as values. These callbacks will be triggered if a response contains
     * the given header and can either issue a subsequent request or manipulate
     * the response as required.
     * By default, the [RequestClient::onHeaderLocation] callback is assigned
     * to the Location header to support automatic redirect following.
     *
     *     $client->headerCallbacks([
     *         'Location' => 'RequestClient::onHeaderLocation',
     *         'WWW-Authenticate' => function($request, $response, $client) {return $new_response;},
     *     ]);
     *
     * @param array $headerCallbacks Array of callbacks to trigger on presence of given headers
     * @return RequestClient|array
     */
    public function headerCallbacks($headerCallbacks = null)
    {
        if (null === $headerCallbacks)
        {
            return $this->_headerCallbacks;
        }
        $this->_headerCallbacks = $headerCallbacks;

        return $this;
    }

    /**
     * Getter and setter for the maximum callback depth property.
     * This protects the main execution from recursive callback execution (eg
     * following infinite redirects, conflicts between callbacks causing loops
     * etc). Requests will only be allowed to nest to the level set by this
     * param before execution is aborted with a Request_Client_Recursion_Exception.
     *
     * @param int $depth Maximum number of callback requests to execute before aborting
     * @return RequestClient|int
     */
    public function maxCallbackDepth($depth = null)
    {
        if (null === $depth)
        {
            return $this->_maxCallbackDepth;
        }

        $this->_maxCallbackDepth = $depth;

        return $this;
    }

    /**
     * Getter/Setter for the callback depth property, which is used to track
     * how many recursions have been executed within the current request execution.
     *
     * @param int $depth Current recursion depth
     * @return RequestClient|int
     */
    public function callbackDepth($depth = null)
    {
        if (null === $depth)
        {
            return $this->_callbackDepth;
        }

        $this->_callbackDepth = $depth;

        return $this;
    }

    /**
     * Getter/Setter for the callbackParams array, which allows additional
     * application-specific parameters to be shared with callbacks.
     *
     *     // Set full array
     *     $client->callbackParams(['foo'=>'bar']);
     *     // Set single key
     *     $client->callbackParams('foo','bar');
     *     // Get full array
     *     $params = $client->callbackParams();
     *     // Get single key
     *     $foo = $client->callbackParams('foo');
     *
     * @param string|array $param
     * @param mixed        $value
     * @return RequestClient|mixed
     */
    public function callbackParams($param = null, $value = null)
    {
        // Getter for full array
        if (null === $param)
        {
            return $this->_callbackParams;
        }

        // Setter for full array
        if (is_array($param))
        {
            $this->_callbackParams = $param;

            return $this;
        }
        // Getter for single value
        elseif (null === $value)
        {
            return Arr::get($this->_callbackParams, $param);
        }
        // Setter for single value
        else
        {
            $this->_callbackParams[$param] = $value;

            return $this;
        }

    }

    /**
     * Assigns the properties of the current RequestClient to another
     * RequestClient instance - used when setting up a subsequent request.
     *
     * @param RequestClient $client
     */
    public function assignClientProperties(RequestClient $client)
    {
        $client->follow($this->follow());
        $client->followHeaders($this->followHeaders());
        $client->headerCallbacks($this->headerCallbacks());
        $client->maxCallbackDepth($this->maxCallbackDepth());
        $client->callbackParams($this->callbackParams());
    }

    /**
     * The default handler for following redirects, triggered by the presence of
     * a Location header in the response.
     * The client's follow property must be set true and the HTTP response status
     * one of 201, 301, 302, 303 or 307 for the redirect to be followed.
     *
     * @param Request   $request
     * @param Response  $response
     * @param RequestClient $client
     * @return  null|Request
     */
    public static function onHeaderLocation(Request $request, Response $response, RequestClient $client)
    {
        // Do we need to follow a Location header ?
        if ($client->follow()
            && in_array($response->status, [
                201,
                301,
                302,
                303,
                307
            ])
        )
        {
            // Figure out which method to use for the follow request
            switch ($response->status)
            {
                default:
                case 301:
                case 307:
                    $followMethod = $request->method;
                    break;
                case 201:
                case 303:
                    $followMethod = Http::GET;
                    break;
                case 302:
                    // Cater for sites with broken HTTP redirect implementations
                    if ($client->strictRedirect())
                    {
                        $followMethod = $request->method;
                    }
                    else
                    {
                        $followMethod = Http::GET;
                    }
                    break;
            }

            // Prepare the additional request, copying any followHeaders that were present on the original request
            $origHeaders = $request->headers()->getArrayCopy();
            $followHeaders = array_intersect_assoc($origHeaders, array_fill_keys($client->followHeaders(), true));

            $followRequest = Request::factory($response->headers('Location'));
            $followRequest->method = $followMethod;
            $followRequest->headers($followHeaders);


            if ($followMethod !== Http::GET)
            {
                $followRequest->body = $request->body;
            }

            return $followRequest;
        }

        return null;
    }

}

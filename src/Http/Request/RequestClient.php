<?php

namespace tourze\Http\Request;

use tourze\Base\Object;
use tourze\Http\Http;
use tourze\Http\Request;
use tourze\Http\Response;
use tourze\Http\Request\Exception\ClientRecursionException;

/**
 * 请求的具体实现类，支持两种请求方式，一种是内部请求，一种是外部请求
 *
 * @property bool  follow
 * @property array followHeaders
 * @property bool  strictRedirect
 * @property array headerCallbacks
 * @property int   maxCallbackDepth
 * @property int   callbackDepth
 * @property array callbackParams
 * @package tourze\Http\Request
 */
abstract class RequestClient extends Object
{

    /**
     * @var bool 如果返回header有跳转，是否继续跟随跳转
     */
    protected $_follow = false;

    /**
     * @var array Headers to preserve when following a redirect
     */
    protected $_followHeaders = ['Authorization'];

    /**
     * @var bool Follow 302 redirect with original request method?
     */
    protected $_strictRedirect = true;

    /**
     * @var array Callbacks to use when response contains given headers
     */
    protected $_headerCallbacks = [
        'Location' => 'tourze\Http\Request\RequestClient::onHeaderLocation'
    ];

    /**
     * @var int header回调函数执行的最大次数
     */
    protected $_maxCallbackDepth = 5;

    /**
     * @var int 当前主请求的级别
     */
    protected $_callbackDepth = 1;

    /**
     * @var array 回调参数
     */
    protected $_callbackParams = [];

    /**
     * @param bool $follow
     * @return RequestClient
     */
    public function setFollow($follow)
    {
        $this->_follow = $follow;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isFollow()
    {
        return $this->_follow;
    }

    /**
     * @param array $followHeaders
     * @return RequestClient
     */
    public function setFollowHeaders($followHeaders)
    {
        $this->_followHeaders = $followHeaders;
        return $this;
    }

    /**
     * @return array
     */
    public function getFollowHeaders()
    {
        return $this->_followHeaders;
    }

    /**
     * @param boolean $strictRedirect
     * @return RequestClient
     */
    public function setStrictRedirect($strictRedirect)
    {
        $this->_strictRedirect = $strictRedirect;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isStrictRedirect()
    {
        return $this->_strictRedirect;
    }

    /**
     * @param array $headerCallbacks
     * @return RequestClient
     */
    public function setHeaderCallbacks($headerCallbacks)
    {
        $this->_headerCallbacks = $headerCallbacks;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaderCallbacks()
    {
        return $this->_headerCallbacks;
    }

    /**
     * @param int $maxCallbackDepth
     * @return RequestClient
     */
    public function setMaxCallbackDepth($maxCallbackDepth)
    {
        $this->_maxCallbackDepth = $maxCallbackDepth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxCallbackDepth()
    {
        return $this->_maxCallbackDepth;
    }

    /**
     * @param int $callbackDepth
     * @return RequestClient
     */
    public function setCallbackDepth($callbackDepth)
    {
        $this->_callbackDepth = $callbackDepth;
        return $this;
    }

    /**
     * @return int
     */
    public function getCallbackDepth()
    {
        return $this->_callbackDepth;
    }

    /**
     * @param array $callbackParams
     * @return RequestClient
     */
    public function setCallbackParams($callbackParams)
    {
        $this->_callbackParams = $callbackParams;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallbackParams()
    {
        return $this->_callbackParams;
    }

    /**
     * 处理请求，根据路由中的信息，执行对应的控制器和动作
     *
     * @param  Request $request
     * @return Response
     * @throws ClientRecursionException
     * @throws Exception\RequestException
     */
    public function execute(Request $request)
    {
        // 防止一直循环
        if ($this->callbackDepth > $this->maxCallbackDepth)
        {
            throw new ClientRecursionException('Could not execute request to :uri - too many recursions after :depth requests', [
                ':uri'   => $request->uri,
                ':depth' => $this->callbackDepth - 1,
            ]);
        }

        $origResponse = $response = Response::factory(['_protocol' => $request->protocol]);

        $response = $this->executeRequest($request, $response);

        foreach ($this->headerCallbacks as $header => $callback)
        {
            if ($response->headers($header))
            {
                $callbackResult = call_user_func($callback, $request, $response, $this);

                if ($callbackResult instanceof Request)
                {
                    // If the callback returns a request, automatically assign client params
                    $this->assignClientProperties($callbackResult->client());
                    $callbackResult
                        ->client()
                        ->callbackDepth = $this->callbackDepth + 1;

                    // Execute the request
                    $response = $callbackResult->execute();
                }
                elseif ($callbackResult instanceof Response)
                {
                    // Assign the returned response
                    $response = $callbackResult;
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
     * 执行请求，并返回接口
     *
     * @param  Request  $request 要处理的request实例
     * @param  Response $response
     * @return Response
     */
    abstract public function executeRequest(Request $request, Response $response);

    /**
     * Assigns the properties of the current RequestClient to another
     * RequestClient instance - used when setting up a subsequent request.
     *
     * @param RequestClient $client
     */
    public function assignClientProperties(RequestClient $client)
    {
        $client->follow = $this->follow;
        $client->followHeaders = $this->followHeaders;
        $client->headerCallbacks = $this->headerCallbacks;
        $client->maxCallbackDepth = $this->maxCallbackDepth;
        $client->callbackParams = $this->callbackParams;
    }

    /**
     * The default handler for following redirects, triggered by the presence of
     * a Location header in the response.
     * The client's follow property must be set true and the HTTP response status
     * one of 201, 301, 302, 303 or 307 for the redirect to be followed.
     *
     * @param Request       $request
     * @param Response      $response
     * @param RequestClient $client
     * @return  null|Request
     */
    public static function onHeaderLocation(Request $request, Response $response, RequestClient $client)
    {
        // Do we need to follow a Location header ?
        if ($client->follow
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
                    if ($client->strictRedirect)
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
            $followHeaders = array_intersect_assoc($origHeaders, array_fill_keys($client->followHeaders, true));

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

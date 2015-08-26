<?php

namespace tourze\Http\Request\Client;

use HttpEncodingException;
use HTTPRequest;
use HttpRequestException;
use tourze\Base\Exception\BaseException;
use tourze\Http\Http;
use tourze\Http\Response;
use tourze\Http\Request;
use tourze\Http\Request\Exception\RequestException;

/**
 * 基于PEAR HTTP扩展实现的外部请求类
 *
 * @property array options
 * @package tourze\Http\Request\Client
 */
class HttpClient extends ExternalClient
{

    /**
     * {@inheritdoc}
     */
    public function __construct($args = [])
    {
        if ( ! http_support(HTTP_SUPPORT_REQUESTS))
        {
            throw new RequestException('Need HTTP request support!');
        }
        parent::__construct($args);
    }

    /**
     * @var  array curl配置项
     * @link http://www.php.net/manual/function.curl-setopt
     */
    protected $_options = [];

    /**
     * @param array $options
     * @return HttpClient
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * 发送HTTP信息
     *
     * @param  Request  $request response to send
     * @param  Response $response
     * @return Response
     * @throws RequestException
     * @throws BaseException
     */
    public function _sendMessage(Request $request, Response $response)
    {
        $httpMethodMapping = [
            Http::GET     => HTTPRequest::METH_GET,
            Http::HEAD    => HTTPRequest::METH_HEAD,
            Http::POST    => HTTPRequest::METH_POST,
            Http::PUT     => HTTPRequest::METH_PUT,
            Http::DELETE  => HTTPRequest::METH_DELETE,
            Http::OPTIONS => HTTPRequest::METH_OPTIONS,
            Http::TRACE   => HTTPRequest::METH_TRACE,
            Http::CONNECT => HTTPRequest::METH_CONNECT,
        ];

        // Create an http request object
        $httpRequest = new HTTPRequest($request->uri, $httpMethodMapping[$request->method]);

        if ($this->_options)
        {
            // Set custom options
            $httpRequest->setOptions($this->options);
        }

        // Set headers
        $httpRequest->setHeaders($request->headers()->getArrayCopy());
        $httpRequest->setCookies($request->cookie());
        $httpRequest->setQueryData($request->query());
        if ($request->method == Http::PUT)
        {
            $httpRequest->addPutData($request->body);
        }
        else
        {
            $httpRequest->setBody($request->body);
        }

        try
        {
            $httpRequest->send();
        }
        catch (HTTPRequestException $e)
        {
            throw new RequestException($e->getMessage());
        }
        catch (HTTPEncodingException $e)
        {
            throw new RequestException($e->getMessage());
        }

        // Build the response
        $response->status = $httpRequest->getResponseCode();
        $response->headers($httpRequest->getResponseHeader());
        $response->cookie($httpRequest->getResponseCookies());
        $response->body = $httpRequest->getResponseBody();

        return $response;
    }

}

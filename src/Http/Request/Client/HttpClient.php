<?php

namespace tourze\Http\Request\Client;

use HttpEncodingException;
use HttpRequestException;
use tourze\Base\Exception\BaseException;
use tourze\Http\Response;
use tourze\Http\Request;
use tourze\Http\Request\Exception\RequestException;

/**
 * [ExternalClient] HTTP driver performs external requests using the
 * php-http extension. To use this driver, ensure the following is completed
 * before executing an external request- ideally in the application bootstrap.
 *
 * @example
 *             // In application bootstrap
 *             ExternalClient::$client = 'HttpClient';
 * @package    Base
 * @category   Base
 * @author     YwiSax
 */
class HttpClient extends ExternalClient
{

    /**
     * Creates a new `RequestClient` object,
     * allows for dependency injection.
     *
     * @param   array $params Params
     *
     * @throws  RequestException
     */
    public function __construct(array $params = [])
    {
        // Check that PECL HTTP supports requests
        if ( ! http_support(HTTP_SUPPORT_REQUESTS))
        {
            throw new RequestException('Need HTTP request support!');
        }

        // Carry on
        parent::__construct($params);
    }

    /**
     * @var     array     curl options
     * @link    http://www.php.net/manual/function.curl-setopt
     */
    protected $_options = [];

    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param  Request  $request response to send
     * @param  Response $response
     *
     * @return Response
     * @throws RequestException
     * @throws BaseException
     */
    public function _sendMessage(Request $request, Response $response)
    {
        $httpMethodMapping = [
            Request::GET     => \HTTPRequest::METH_GET,
            Request::HEAD    => \HTTPRequest::METH_HEAD,
            Request::POST    => \HTTPRequest::METH_POST,
            Request::PUT     => \HTTPRequest::METH_PUT,
            Request::DELETE  => \HTTPRequest::METH_DELETE,
            Request::OPTIONS => \HTTPRequest::METH_OPTIONS,
            Request::TRACE   => \HTTPRequest::METH_TRACE,
            Request::CONNECT => \HTTPRequest::METH_CONNECT,
        ];

        // Create an http request object
        $httpRequest = new \HTTPRequest($request->uri, $httpMethodMapping[$request->method]);

        if ($this->_options)
        {
            // Set custom options
            $httpRequest->setOptions($this->_options);
        }

        // Set headers
        $httpRequest->setHeaders($request->headers()->getArrayCopy());
        $httpRequest->setCookies($request->cookie());
        $httpRequest->setQueryData($request->query());
        if ($request->method == Request::PUT)
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

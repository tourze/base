<?php

namespace tourze\Http\Request\Client;

use tourze\Base\Exception\BaseException;
use tourze\Http\HttpResponse;
use tourze\Http\HttpRequest;

/**
 * [ExternalClient] Stream driver performs external requests using php
 * sockets. To use this driver, ensure the following is completed
 * before executing an external request- ideally in the application bootstrap.
 *
 * @example
 *             // In application bootstrap
 *             ExternalClient::$client = 'StreamClient';
 * @package    Base
 * @category   Base
 * @author     YwiSax
 */
class StreamClient extends ExternalClient
{

    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param  HttpRequest  $request response to send
     * @param  HttpResponse $response
     *
     * @return HttpResponse
     * @throws BaseException
     */
    public function _sendMessage(HttpRequest $request, HttpResponse $response)
    {
        // Calculate stream mode
        $mode = ($request->method === HttpRequest::GET) ? 'r' : 'r+';

        // Process cookies
        if ($cookies = $request->cookie())
        {
            $request->headers('cookie', http_build_query($cookies, null, '; '));
        }

        // Get the message body
        $body = $request->body;

        if (is_resource($body))
        {
            $body = stream_get_contents($body);
        }

        // Set the content length
        $request->headers('content-length', (string) strlen($body));

        list($protocol) = explode('/', $request->protocol);

        // Create the context
        $options = [
            strtolower($protocol) => [
                'method'  => $request->method,
                'header'  => (string) $request->headers(),
                'content' => $body
            ]
        ];

        // Create the context stream
        $context = stream_context_create($options);
        stream_context_set_option($context, $this->_options);

        $uri = $request->uri;

        if ($query = $request->query())
        {
            $uri .= '?' . http_build_query($query, null, '&');
        }

        $stream = fopen($uri, $mode, false, $context);

        $metaData = stream_get_meta_data($stream);

        // Get the HTTP response code
        $httpResponse = array_shift($metaData['wrapper_data']);

        if (false !== preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $httpResponse, $matches))
        {
            $protocol = $matches[1][0];
            $status = (int) $matches[2][0];
        }
        else
        {
            $protocol = null;
            $status = null;
        }

        // Get any existing response headers
        $responseHeader = $response->headers();

        // Process headers
        array_map([
            $responseHeader,
            'parseHeaderString'
        ], [], $metaData['wrapper_data']);

        $response->status = $status;
        $response->protocol = $protocol;
        $response->body = stream_get_contents($stream);

        // Close the stream after use
        fclose($stream);

        return $response;
    }

}

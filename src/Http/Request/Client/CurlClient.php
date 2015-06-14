<?php

namespace tourze\Http\Request\Client;

use tourze\Base\Exception\BaseException;
use tourze\Http\HttpResponse;
use tourze\Http\HttpRequest;
use tourze\Http\Request\Exception\RequestException;

/**
 * [ExternalClient] Curl driver performs external requests using the
 * php-curl extension. This is the default driver for all external requests.
 *
 * @package    Base
 * @category   Base
 * @author     YwiSax
 */
class CurlClient extends ExternalClient
{

    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param   HttpRequest  $request response to send
     * @param   HttpResponse $response
     *
     * @return  HttpResponse
     * @throws  RequestException
     * @throws  BaseException
     */
    public function _sendMessage(HttpRequest $request, HttpResponse $response)
    {
        $options = [];
        // Set the request method
        $options = $this->_setCurlRequestMethod($request, $options);

        // Set the request body. This is perfectly legal in CURL even
        // if using a request other than POST. PUT does support this method
        // and DOES NOT require writing data to disk before putting it, if
        // reading the PHP docs you may have got that impression. SdF
        $options[CURLOPT_POSTFIELDS] = $request->body;

        // Process headers
        if ($headers = $request->headers())
        {
            $httpHeaders = [];
            foreach ($headers as $key => $value)
            {
                $httpHeaders[] = $key . ': ' . $value;
            }
            $options[CURLOPT_HTTPHEADER] = $httpHeaders;
        }

        // Process cookies
        if ($cookies = $request->cookie())
        {
            $options[CURLOPT_COOKIE] = http_build_query($cookies, null, '; ');
        }

        // Get any existing response headers
        $responseHeader = $response->headers();

        // Implement the standard parsing parameters
        $options[CURLOPT_HEADERFUNCTION] = [
            $responseHeader,
            'parseHeaderString'
        ];
        $this->_options[CURLOPT_RETURNTRANSFER] = true;
        $this->_options[CURLOPT_HEADER] = false;

        // Apply any additional options set to
        $options += $this->_options;
        $uri = $request->uri;
        if ($query = $request->query())
        {
            $uri .= '?' . http_build_query($query, null, '&');
        }

        // Open a new remote connection
        $curl = curl_init($uri);
        // Set connection options
        if ( ! curl_setopt_array($curl, $options))
        {
            throw new RequestException('Failed to set CURL options, check CURL documentation: :url', [
                ':url' => 'http://php.net/curl_setopt_array'
            ]);
        }

        // Get the response body
        $body = curl_exec($curl);
        // Get the response information
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (false === $body)
        {
            $error = curl_error($curl);
        }
        // Close the connection
        curl_close($curl);

        if (isset($error))
        {
            throw new RequestException('Error fetching remote :url [ status :code ] :error', [
                ':url'   => $request->url(),
                ':code'  => $code,
                ':error' => $error
            ]);
        }
        $response->status = $code;
        $response->body = $body;

        return $response;
    }

    /**
     * Sets the appropriate curl request options.
     *
     * @param HttpRequest $request
     * @param array       $options
     * @return array
     */
    public function _setCurlRequestMethod(HttpRequest $request, array $options)
    {
        switch ($request->method)
        {
            case HttpRequest::POST:
                $options[CURLOPT_POST] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $request->method;
                break;
        }

        return $options;
    }

}

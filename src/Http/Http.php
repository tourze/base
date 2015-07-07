<?php

namespace tourze\Http;

use tourze\Base\Exception\BaseException;
use tourze\Http\Exception\Http304Exception;
use tourze\Http\Exception\HttpException;
use tourze\Http\Exception\RedirectException;

/**
 * 包含了一些http操作相关的基础信息和助手方法
 *
 * @package    Base
 * @category   HTTP
 * @author     YwiSax
 */
abstract class Http
{

    // HTTP方法列表
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE   = 'TRACE';
    const CONNECT = 'CONNECT';
    const MERGE   = 'MERGE';
    const PATCH   = 'PATCH';
    const COPY    = 'COPY';

    /**
     * @var  string  默认HTTP协议
     */
    public static $protocol = 'HTTP/1.1';

    /**
     * 跳转
     *
     * @param  string $uri  要跳转的URI
     * @param  int    $code 跳转时使用的http状态码
     *
     * @throws HttpException
     * @throws BaseException
     */
    public static function redirect($uri = '', $code = 302)
    {
        $e = HttpException::factory($code);
        if ( ! $e instanceof RedirectException)
        {
            throw new BaseException("Invalid redirect code ':code'", [
                ':code' => $code,
            ]);
        }
        throw $e->location($uri);
    }

    /**
     * Checks the browser cache to see the response needs to be returned,
     * execution will halt and a 304 Not Modified will be sent if the
     * browser cache is up to date.
     *
     * @param  Request  $request  Request
     * @param  Response $response Response
     * @param  string   $etag     Resource ETag
     *
     * @throws Http304Exception
     * @return Response
     */
    public static function checkCache(Request $request, Response $response, $etag = null)
    {
        // 为空的话，生成新的etag
        if (null == $etag)
        {
            $etag = $response->generateEtag();
        }
        $response->headers('etag', $etag);

        // Add the Cache-Control header if it is not already set
        if ($response->headers('cache-control'))
        {
            $response->headers('cache-control', $response->headers('cache-control') . ', must-revalidate');
        }
        else
        {
            $response->headers('cache-control', 'must-revalidate');
        }

        // Check if we have a matching etag
        if ($request->headers('if-none-match') && (string) $request->headers('if-none-match') === $etag)
        {
            // No need to send data again
            throw (new Http304Exception())->headers('etag', $etag);
        }

        return $response;
    }

    /**
     * Parses a HTTP header string into an associative array
     *
     * @param   string $headerString Header string to parse
     *
     * @return  Header
     */
    public static function parseHeaderString($headerString)
    {
        // If the PECL HTTP extension is loaded
        if (extension_loaded('http'))
        {
            // Use the fast method to parse header string
            return new Header(http_parse_headers($headerString));
        }

        // Otherwise we use the slower PHP parsing
        $headers = [];

        // Match all HTTP headers
        if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $headerString, $matches))
        {
            // Parse each matched header
            foreach ($matches[0] as $key => $value)
            {
                // If the header has not already been set
                if ( ! isset($headers[$matches[1][$key]]))
                {
                    // Apply the header directly
                    $headers[$matches[1][$key]] = $matches[2][$key];
                }
                // Otherwise there is an existing entry
                else
                {
                    // If the entry is an array
                    if (is_array($headers[$matches[1][$key]]))
                    {
                        // Apply the new entry to the array
                        $headers[$matches[1][$key]][] = $matches[2][$key];
                    }
                    // Otherwise create a new array with the entries
                    else
                    {
                        $headers[$matches[1][$key]] = [
                            $headers[$matches[1][$key]],
                            $matches[2][$key],
                        ];
                    }
                }
            }
        }

        // Return the headers
        return new Header($headers);
    }

    /**
     * Parses the the HTTP request headers and returns an array containing
     * key value pairs. This method is slow, but provides an accurate
     * representation of the HTTP request.
     *
     *      // Get http headers into the request
     *      $request->headers = HTTP::requestHeaders();
     *
     * @return  Header
     */
    public static function requestHeaders()
    {
        // If running on apache server
        if (function_exists('apache_request_headers'))
        {
            // Return the much faster method
            return new Header(apache_request_headers());
        }
        // If the PECL HTTP tools are installed
        elseif (extension_loaded('http'))
        {
            // Return the much faster method
            return new Header(http_get_request_headers());
        }

        // Setup the output
        $headers = [];

        // Parse the content type
        if ( ! empty($_SERVER['CONTENT_TYPE']))
        {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        // Parse the content length
        if ( ! empty($_SERVER['CONTENT_LENGTH']))
        {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }

        foreach ($_SERVER as $key => $value)
        {
            // If there is no HTTP header here, skip
            if (strpos($key, 'HTTP_') !== 0)
            {
                continue;
            }

            // This is a dirty hack to ensure HTTP_X_FOO_BAR becomes x-foo-bar
            $headers[str_replace([
                'HTTP_',
                '_'
            ], [
                '',
                '-'
            ], $key)] = $value;
        }

        return new Header($headers);
    }
}

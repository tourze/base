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

        // 检测是否有合适的etag
        if ($request->headers('if-none-match') && (string) $request->headers('if-none-match') === $etag)
        {
            // No need to send data again
            throw (new Http304Exception())->headers('etag', $etag);
        }

        return $response;
    }

    /**
     * 解析header数据为一个关联数组
     *
     * @param  string $str 要解析的字符串
     * @return array
     */
    public static function parseHeaderString($str)
    {
        // If the PECL HTTP extension is loaded
        if (extension_loaded('http'))
        {
            // Use the fast method to parse header string
            return http_parse_headers($str);
        }

        $headers = [];

        // Match all HTTP headers
        if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $str, $matches))
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

        return $headers;
    }

    /**
     * 解析请求，并读取其中的HEADER信息
     *
     * @return array
     */
    public static function requestHeaders()
    {
        // apache服务器
        if (function_exists('apache_request_headers'))
        {
            return apache_request_headers();
        }

        // PECL扩展加载了
        elseif (extension_loaded('http'))
        {
            return http_get_request_headers();
        }

        $headers = [];

        if ( ! empty($_SERVER['CONTENT_TYPE']))
        {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        if ( ! empty($_SERVER['CONTENT_LENGTH']))
        {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }

        foreach ($_SERVER as $key => $value)
        {
            // 跳过非HTTP开头的值
            if (strpos($key, 'HTTP_') !== 0)
            {
                continue;
            }

            $key = str_replace(['HTTP_', '_'], ['', '-'], $key);
            $key = strtolower($key);
            $headers[$key] = $value;
        }

        return $headers;
    }
}

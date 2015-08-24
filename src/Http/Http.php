<?php

namespace tourze\Http;

use tourze\Base\Exception\BaseException;
use tourze\Http\Exception\Http304Exception;
use tourze\Http\Exception\HttpException;
use tourze\Http\Exception\RedirectException;
use tourze\Server\Protocol\Http as ServerHttp;

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
     * 注意，不要直接使用php自带的header，而要使用这个方法
     *
     * @param string    $content
     * @param bool|true $replace
     * @param int       $httpResponseCode
     * @return bool
     */
    public static function header($content, $replace = true, $httpResponseCode = 0)
    {
        return ServerHttp::header($content, $replace, $httpResponseCode);
    }

    /**
     * 作用等于php自带的[header_remove]
     *
     * @param string $name
     */
    public static function headerRemove($name)
    {
        ServerHttp::headerRemove($name);
    }

    /**
     * 写cookie，注意不要直接使用[setcookie]函数
     *
     * @param string     $name
     * @param string     $value
     * @param int        $maxAge
     * @param string     $path
     * @param string     $domain
     * @param bool|false $secure
     * @param bool|false $httpOnly
     * @return bool
     */
    public static function setCookie($name, $value = '', $maxAge = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
    {
        return ServerHttp::setcookie($name, $value, $maxAge, $path, $domain, $secure, $httpOnly);
    }

    /**
     * 替代php自带的[session_start]函数
     *
     * @return bool
     */
    public static function sessionStart()
    {
        return ServerHttp::sessionStart();
    }

    /**
     * 替代php自带的[session_write_close]函数
     *
     * @return bool
     */
    public static function sessionWriteClose()
    {
        return ServerHttp::sessionWriteClose();
    }

    /**
     * 代替php的exit和die
     *
     * @param string $msg
     * @throws \Exception
     */
    public static function end($msg = '')
    {
        ServerHttp::end($msg);
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

<?php

namespace tourze\Base\Component;

/**
 * 基础的HTTP组件
 *
 * @package tourze\Base
 */
class Http
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
     * 退出当前http请求
     *
     * @param string $msg
     */
    public function end($msg = '')
    {
        echo $msg;
        exit;
    }

    /**
     * 写cookie，注意不要直接使用[setcookie]函数
     *
     * @param string $name
     * @param string $value
     * @param int    $maxAge
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return bool
     */
    public function setCookie($name, $value = '', $maxAge = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
    {
        return setcookie($name, $value, $maxAge, $path, $domain, $secure, $httpOnly);
    }
}

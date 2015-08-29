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
     * 写cookie
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

    /**
     * 开始会话
     *
     * @return bool
     */
    public function sessionStart()
    {
        return session_start();
    }

    /**
     * 返回会话ID
     *
     * @param mixed $id
     * @return string
     */
    public function sessionID($id = null)
    {
        return session_id($id);
    }

    /**
     * 重新返回一个会话ID
     *
     * @param bool|false $deleteOldSession
     * @return bool
     */
    public function sessionRegenerateID($deleteOldSession = false)
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * 结束会话
     */
    public function sessionWriteClose()
    {
        session_write_close();
    }

    /**
     * 输出头部信息
     *
     * @param string    $string
     * @param bool|true $replace
     * @param null      $httpResponseCode
     */
    public function header($string, $replace = true, $httpResponseCode = null)
    {
        header($string, $replace, $httpResponseCode);
    }

    /**
     * @param string $name
     */
    public function headerRemove($name = null)
    {
        header_remove($name);
    }

    /**
     * @return array
     */
    public function headersList()
    {
        return headers_list();
    }

    /**
     * @param string $file
     * @param string $line
     * @return bool
     */
    public function headersSent(&$file = null, &$line = null)
    {
       return headers_sent($file, $line);
    }
}

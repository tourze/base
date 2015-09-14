<?php

namespace tourze\Base\Component;

use tourze\Base\ComponentInterface;

/**
 * HTTP组件要实现的接口
 *
 * @package tourze\Base\Component
 */
interface HttpInterface extends ComponentInterface
{

    /**
     * 退出当前http请求
     *
     * @param string $msg
     */
    public function end($msg = '');

    /**
     * 输出指定code
     *
     * @param int $code
     */
    public function code($code);

    /**
     * 跳转
     *
     * @param  string $uri  要跳转的URI
     * @param  int    $code 跳转时使用的http状态码
     */
    public function redirect($uri = '', $code = 302);

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
    public function setCookie($name, $value = '', $maxAge = 0, $path = '', $domain = '', $secure = false, $httpOnly = false);

    /**
     * 开始会话
     *
     * @return bool
     */
    public function sessionStart();

    /**
     * 返回会话ID
     *
     * @param mixed $id
     * @return string
     */
    public function sessionID($id = null);

    /**
     * 重新返回一个会话ID
     *
     * @param bool|false $deleteOldSession
     * @return bool
     */
    public function sessionRegenerateID($deleteOldSession = false);

    /**
     * 结束会话
     */
    public function sessionWriteClose();

    /**
     * 输出头部信息
     *
     * @param string    $string
     * @param bool|true $replace
     * @param null|int  $httpResponseCode
     */
    public function header($string, $replace = true, $httpResponseCode = null);

    /**
     * 删除指定的header信息
     *
     * @param string $name
     */
    public function headerRemove($name = null);

    /**
     * 返回当前发送的header数组
     *
     * @return array
     */
    public function headersList();

    /**
     * 返回已经发送的头信息
     *
     * @param string $file
     * @param string $line
     * @return bool
     */
    public function headersSent(&$file = null, &$line = null);
}

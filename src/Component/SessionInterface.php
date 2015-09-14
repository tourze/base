<?php

namespace tourze\Base\Component;

use tourze\Base\ComponentInterface;

/**
 * 会话相关接口
 *
 * @package tourze\Base\Component
 */
interface SessionInterface extends ComponentInterface
{

    /**
     * 开始会话
     */
    public function start();

    /**
     * 返回当前会话ID
     *
     * @param string $id
     * @return string
     */
    public function id($id = null);

    /**
     * 读取指定会话的值
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * 设置会话值
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);

    /**
     * 移除指定会话key
     *
     * @param string $name
     */
    public function remove($name);

    /**
     * 清空会话数据
     *
     * @return bool
     */
    public function destroy();
}

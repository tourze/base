<?php

namespace tourze\Base\Component;

use tourze\Base\Base;
use tourze\Base\Component;
use tourze\Base\Helper\Arr;

/**
 * 会话处理组件
 *
 * @package tourze\Base\Component
 */
class Session extends Component
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->start();
    }

    /**
     * 开始会话
     */
    public function start()
    {
        Base::getHttp()->sessionStart();
    }

    /**
     * 读取指定会话的值
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return Arr::get($_SESSION, $name, $default);
    }

    /**
     * 设置会话值
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * 移除指定会话key
     *
     * @param string $name
     */
    public function remove($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * 清空会话数据
     *
     * @return bool
     */
    public function destroy()
    {
        return session_destroy();
    }
}
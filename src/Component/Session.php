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
    public $persistence = false;

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
        Base::getLog()->debug(__METHOD__ . ' session start');
        Base::getHttp()->sessionStart();
    }

    /**
     * 返回当前会话ID
     *
     * @param string $id
     * @return string
     */
    public function id($id = null)
    {
        Base::getLog()->debug(__METHOD__ . ' get session id');
        return session_id($id);
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
        Base::getLog()->debug(__METHOD__ . ' get session', [
            'name'    => $name,
            'default' => $default,
        ]);
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
        Base::getLog()->debug(__METHOD__ . ' set session', [
            'name'  => $name,
            'value' => $value,
        ]);
        $_SESSION[$name] = $value;
    }

    /**
     * 移除指定会话key
     *
     * @param string $name
     */
    public function remove($name)
    {
        Base::getLog()->debug(__METHOD__ . ' remove session', [
            'name' => $name,
        ]);
        unset($_SESSION[$name]);
    }

    /**
     * 清空会话数据
     *
     * @return bool
     */
    public function destroy()
    {
        Base::getLog()->debug(__METHOD__ . ' destroy session');
        return session_destroy();
    }
}

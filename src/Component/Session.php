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
class Session extends Component implements SessionInterface
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
     * {@inheritdoc}
     */
    public function start()
    {
        Base::getLog()->debug(__METHOD__ . ' session start');
        Base::getHttp()->sessionStart();
    }

    /**
     * {@inheritdoc}
     */
    public function id($id = null)
    {
        Base::getLog()->debug(__METHOD__ . ' get session id');
        return session_id($id);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function remove($name)
    {
        Base::getLog()->debug(__METHOD__ . ' remove session', [
            'name' => $name,
        ]);
        unset($_SESSION[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        Base::getLog()->debug(__METHOD__ . ' destroy session');
        return session_destroy();
    }
}

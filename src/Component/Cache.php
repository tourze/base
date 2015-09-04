<?php

namespace tourze\Base\Component;

use tourze\Base\Component;

/**
 * 默认缓存组件，不做任何事
 *
 * @property int expired
 * @package tourze\Base\Component
 */
class Cache extends Component
{

    /**
     * @var int 默认过期时间
     */
    protected $_expired;

    /**
     * @return int
     */
    public function getExpired()
    {
        return $this->_expired;
    }

    /**
     * @param int $expired
     */
    public function setExpired($expired)
    {
        $this->_expired = $expired;
    }

    /**
     * 读取指定key的缓存
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return null;
    }

    /**
     * 保存缓存
     *
     * @param string $name
     * @param mixed  $value
     * @param int    $expired
     * @return bool
     */
    public function set($name, $value, $expired = null)
    {
        return true;
    }

    /**
     * 删除缓存
     *
     * @param string $name
     * @return bool
     */
    public function remove($name)
    {
        return true;
    }
}

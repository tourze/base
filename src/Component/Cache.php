<?php

namespace tourze\Base\Component;

use tourze\Base\Component;
use tourze\Base\Helper\Arr;

/**
 * 默认缓存组件，使用内存来做缓存
 *
 * @property int expired
 * @package tourze\Base\Component
 */
class Cache extends Component
{

    /**
     * @var array 保存缓存的容器
     */
    protected $_cache = [];

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
     * @param mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ( ! isset($this->_cache[$name]))
        {
            return $default;
        }

        $data = Arr::get($this->_cache, $name);

        // 判断过期时间
        $expired = Arr::get($data, 'expired');
        if (time() > $expired)
        {
            $this->remove($name);
            return $default;
        }

        $value = Arr::get($data, 'value');
        return $value;
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
        $this->_cache[$name] = [
            'value'   => $value,
            'expired' => time() + $expired,
        ];
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
        unset($this->_cache[$name]);
        return true;
    }
}

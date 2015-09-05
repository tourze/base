<?php

namespace tourze\Base\Component;

use tourze\Base\Base;
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
        Base::getLog()->debug(__METHOD__ . ' get cache', [
            'name'    => $name,
            'default' => $default,
        ]);

        if ( ! isset($this->_cache[$name]))
        {
            Base::getLog()->debug(__METHOD__ . ' cache not found, return default value', [
                'name'    => $name,
                'default' => $default,
            ]);
            return $default;
        }

        $data = Arr::get($this->_cache, $name);

        // 判断过期时间
        $current = time();
        $expired = Arr::get($data, 'expired');
        if ($current > $expired)
        {
            Base::getLog()->debug(__METHOD__ . ' cache is expired', [
                'name'    => $name,
                'current' => $current,
                'expired' => $expired,
            ]);
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
        // 过期时间自动加上当前时间戳
        $expired = time() + $expired;

        Base::getLog()->debug(__METHOD__ . ' save cache', [
            'name'    => $name,
            'type'    => gettype($value),
            'expired' => $expired,
        ]);
        $this->_cache[$name] = [
            'value'   => $value,
            'expired' => $expired,
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
        Base::getLog()->debug(__METHOD__ . ' remove cache', [
            'name' => $name,
        ]);
        unset($this->_cache[$name]);
        return true;
    }
}

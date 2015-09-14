<?php

namespace tourze\Base\Component;

use tourze\Base\Base;
use tourze\Base\Component;
use tourze\Base\Helper\Arr;

/**
 * 默认缓存组件，使用内存来做缓存
 *
 * @package tourze\Base\Component
 */
class Cache extends Component implements CacheInterface
{

    /**
     * @var array 保存缓存的容器
     */
    protected $_cache = [];

    /**
     * @var int 默认过期时间
     */
    public $expired;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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

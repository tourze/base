<?php

namespace tourze\Base\Component;

use tourze\Base\Base;
use tourze\Base\Component;
use tourze\Base\Helper\Arr;

/**
 * 默认缓存组件，使用内存来做缓存
 * 注意，这个缓存组件不太可靠，最好使用redis等KV数据库来实现缓存
 * 缓存回收使用LRU算法
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
     * @var int[] 缓存命中记录器
     */
    protected $_cacheHit = [];

    /**
     * @var int 默认过期时间
     */
    public $expired;

    /**
     * @var int 超过这个数目，就会自动缩减缓存数据
     */
    public $maxLine = 20000;

    /**
     * @var int 最大超时时间
     */
    public $maxExpire = 3600;

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

        // 判断过期时间，过期的话，直接删除
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
        $this->saveHitTime($name);
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value, $expired = null)
    {
        $this->checkSize();

        // 过期时间自动加上当前时间戳
        if ($expired > $this->maxExpire)
        {
            $expired = $this->maxExpire;
        }
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
        $this->_cacheHit[$name] = time();

        $this->checkSize();
        return true;
    }

    /**
     * 保存最新命中时间
     *
     * @param string $name
     */
    private function saveHitTime($name)
    {
        $this->_cacheHit[$name] = time();
    }

    /**
     * 检查内存中的缓存尺寸
     */
    private function checkSize()
    {
        // 到达缓存记录数的警戒线啦
        if (count($this->_cache) >= $this->maxLine)
        {
            asort($this->_cacheHit);
            $longestTime = current($this->_cacheHit);
            reset($this->_cacheHit);

            /*
            if (($longestTime + $this->maxExpire) < time())
            {
                $subTime = time() - $this->maxExpire;
                foreach ($this->_cacheHit as $key => $cacheTime)
                {
                    if ($cacheTime)
                }
            }
            else
            {

            }
            */

            $cutLength = intval($this->maxLine / 10);
            foreach ($this->_cacheHit as $key => $cacheTime)
            {
                if ($cutLength <= 0)
                {
                    break;
                }
                $this->remove($key);
                $cutLength--;
            }
        }
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
        unset($this->_cacheHit[$name]);
        return true;
    }
}

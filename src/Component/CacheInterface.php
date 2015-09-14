<?php

namespace tourze\Base\Component;

use tourze\Base\ComponentInterface;

/**
 * 缓存组件的实现接口
 *
 * @package tourze\Base\Component
 */
interface CacheInterface extends ComponentInterface
{

    /**
     * 读取指定key的缓存
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * 保存缓存
     *
     * @param string $name
     * @param mixed  $value
     * @param int    $expired 过期秒数
     * @return bool
     */
    public function set($name, $value, $expired = null);

    /**
     * 删除缓存
     *
     * @param string $name
     * @return bool
     */
    public function remove($name);
}

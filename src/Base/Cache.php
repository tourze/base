<?php

namespace tourze\Base;

/**
 * 基于doctrine/cache实现的缓存类
 *
 * @package tourze\Base
 */
class Cache extends Object
{

    /**
     * @var array 配置信息
     */
    protected $config;

    /**
     * @var
     */
    protected $doctrineCache;

    /**
     * 缓存单例入口
     *
     * @param null $args
     * @return $this
     */
    public static function instance($args = null)
    {
        if ($args === null)
        {
            $args = 'default';
        }

        return parent::instance($args);
    }

    /**
     * 构造一个doctrine/cache对象
     */
    public function init()
    {

    }
}

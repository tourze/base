<?php

namespace tourze\NoSQL\Redis;

use tourze\Base\Config;
use tourze\Base\Helper\Arr;
use tourze\Base\Object;
use tourze\NoSQL\Exception\NoSQLException;

/**
 * Redis包装类
 *
 * @package tourze\Redis
 */
class Redis extends Object
{

    /**
     * @var mixed
     */
    public $parameters;

    /**
     * @var mixed
     */
    public $options;

    /**
     * 获取指定的配置组信息，并返回实例
     *
     * @param string $key
     * @return \tourze\NoSQL\Redis\Client
     * @throws \tourze\NoSQL\Exception\NoSQLException
     */
    public static function instance($key = 'default')
    {
        $instanceKey = self::instanceKey($key);

        if ( ! isset(self::$_instances[$instanceKey]))
        {
            $config = Config::load('redis')->get($key);
            if ( ! $config)
            {
                throw new NoSQLException('The requested config group not found.');
            }
            self::$_instances[$instanceKey] = new Client(Arr::get($config, 'parameters'), Arr::get($config, 'options'));
        }

        return self::$_instances[$key];
    }

}

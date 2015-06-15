<?php

namespace tourze\Redis;

use tourze\Base\Config;
use tourze\Base\Helper\Arr;
use tourze\Base\Object;

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
     * @param string $key
     * @return Client
     */
    public static function instance($key = 'default')
    {
        if ( ! isset(self::$_instances[$key]))
        {
            $config = Config::load('redis')->get($key);
            self::$_instances[$key] = new Client(Arr::get($config, 'parameters'), Arr::get($config, 'options'));
        }

        return self::$_instances[$key];
    }

}

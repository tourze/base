<?php

namespace tourze\Server;

use tourze\Base\Config;
use tourze\Base\Helper\Arr;
use tourze\Server\Exception\BaseException;
use Workerman\Worker as BaseWorker;

/**
 * 继承原有的Worker基础类
 *
 * @package tourze\Server
 */
class Worker extends BaseWorker
{

    /**
     * 修改原构造方法
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct(Arr::get($config, 'socketName'), Arr::get($config, 'contextOptions'));

        // 设置数据
        foreach ($config as $k => $v)
        {
            if (isset($this->$k))
            {
                $this->$k = $v;
            }
        }
    }

    /**
     * 加载指定的配置
     *
     * @param string $name
     * @return bool
     * @throws \tourze\Server\Exception\BaseException
     */
    public static function load($name)
    {
        if ( ! $config = Config::load('core')->get($name))
        {
            throw new BaseException('The requested config not found.');
        }

        if ( ! $socketName = Arr::get($config, 'socketName'))
        {
            throw new BaseException('The socket name should not be empty.');
        }

        if (substr($socketName, 0, 4) == 'http')
        {
            new Web($config);
        }
        else
        {
            new Worker($config);
        }

        return true;
    }

}

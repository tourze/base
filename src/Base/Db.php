<?php

namespace tourze\Base;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * 数据库连接类，基于doctrine/dbal来实现
 *
 * @package    Base/Database
 * @category   Base
 */
class Db
{

    public static $configFile = 'database';

    /**
     * @const  int  SELECT查询
     */
    const SELECT = 1;

    /**
     * @const  int  INSERT查询
     */
    const INSERT = 2;

    /**
     * @const  int  UPDATE查询
     */
    const UPDATE = 3;

    /**
     * @const  int  DELETE查询
     */
    const DELETE = 4;

    /**
     * @var  string  默认实例名
     */
    public static $default = 'default';

    /**
     * @var  array  一个存放所有实例的数组
     */
    public static $instances = [];

    /**
     * @var array  额外的数据库字段格式支持
     */
    public static $mappingType = [
        'enum'      => 'string',
        'set'       => 'string',
        'varbinary' => 'string',
    ];

    /**
     * 单例模式，获取一个指定的实例
     *
     *     // 加载默认实例
     *     $db = Database::instance();
     *     // 指定实例名称和配置
     *     $db = Database::instance('custom', $config);
     *
     * @param   string $name   实例名
     * @param   array  $config 配置参数
     * @return  Connection
     */
    public static function instance($name = null, array $config = null)
    {
        if (null === $name)
        {
            $name = Db::$default;
        }

        if ( ! isset(Db::$instances[$name]))
        {
            // 读取配置
            if (null === $config)
            {
                $config = (array) Config::load(self::$configFile)->get($name);
            }

            $conn = DriverManager::getConnection($config);

            $platform = $conn->getDatabasePlatform();
            foreach (self::$mappingType as $dbType => $doctrineType)
            {
                if ( ! $platform->hasDoctrineTypeMappingFor($dbType))
                {
                    $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
                }
            }

            Db::$instances[$name] = $conn;
        }

        return Db::$instances[$name];
    }
}

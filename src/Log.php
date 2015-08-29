<?php

namespace tourze\Base;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * 日志记录类
 *
 * @package tourze\Base
 */
class Log
{

    /**
     * @var Logger
     */
    protected static $_instance = null;

    /**
     * @var mixed 当前日志配置
     */
    protected static $_config = null;

    /**
     * 配置日志信息
     * 在正式使用日志功能前要调用该方法
     *
     * @param string|array $config 配置文件路径、或者一个存放配置信息的数组
     */
    public static function configure($config = null)
    {
        self::$_config = $config;
    }

    /**
     * 返回一个日志记录器实例
     *
     * @return Logger
     */
    public static function instance()
    {
        if (self::$_instance === null)
        {
            self::$_instance = new Logger('default');

            $logFile = Base::$logConfig['file'];
            @mkdir(dirname($logFile), 0666, true);

            self::$_instance->pushHandler(new StreamHandler($logFile));
        }

        return self::$_instance;
    }

    /**
     * 调试信息
     *
     * @param       $log
     * @param array $context
     */
    public static function debug($log, array $context = [])
    {
        self::instance()->addDebug($log, $context);
    }

    /**
     * Info级别
     *
     * @param string $log
     * @param array  $context
     */
    public static function info($log, array $context = [])
    {
        self::instance()->addInfo($log, $context);
    }

    /**
     * Notice级别
     *
     * @param string $log
     * @param array  $context
     */
    public static function notice($log, array $context = [])
    {
        self::instance()->addNotice($log, $context);
    }

    /**
     * 警告信息
     *
     * @param string $log
     * @param array  $context
     */
    public static function warning($log, array $context = [])
    {
        self::instance()->addWarning($log, $context);
    }

    /**
     * 错误信息
     *
     * @param string $log
     * @param array  $context
     */
    public static function error($log, array $context = [])
    {
        self::instance()->addError($log, $context);
    }

    /**
     * critical级别
     *
     * @param string $log
     * @param array  $context
     */
    public static function critical($log, array $context = [])
    {
        self::instance()->addCritical($log, $context);
    }

    /**
     * alert级别
     *
     * @param string $log
     * @param array  $context
     */
    public static function alert($log, array $context = [])
    {
        self::instance()->addAlert($log, $context);
    }

    /**
     * emergency级别
     *
     * @param string $log
     * @param array  $context
     */
    public static function emergency($log, array $context = [])
    {
        self::instance()->addEmergency($log, $context);
    }
}

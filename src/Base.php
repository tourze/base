<?php

namespace tourze\Base;

use tourze\Base\Exception\BaseException;
use tourze\Base\Exception\ComponentClassNotFoundException;
use tourze\Base\Exception\ComponentNotFoundException;
use tourze\Base\Helper\Arr;

/**
 * 基础类，包含一些最基础和常用的操作：
 *
 * - 环境初始化
 * - 类的自动加载
 * - 助手traits
 *
 * @package tourze\Base
 */
class Base extends Object
{

    /**
     * @const string 当前框架版本
     */
    const VERSION = 'beta';

    /**
     * @var string 默认输出的内容类型
     */
    public static $contentType = 'text/html';

    /**
     * @var string 当前服务器名称
     */
    public static $serverName = '';

    /**
     * @var array 当前实例的主机名列表
     */
    public static $hostNames = [];

    /**
     * @var string 当前应用所在路径，相对于web根目录
     */
    public static $baseUrl = '/';

    /**
     * @var string 缺省文件名称
     */
    public static $indexFile = false;

    /**
     * @var bool 自定义X-Powered-By
     */
    public static $expose = false;

    /**
     * @var Component[] 组件缓存列表
     */
    public static $components = [];

    /**
     * 清理组件实例，将非持久化的，可删除的组件实例删除
     */
    public static function cleanComponents()
    {
        foreach (self::$components as $name => $component)
        {
            if ( ! $component->persistence)
            {
                self::$components[$name] = null;
                unset(self::$components[$name]);
            }
        }
    }

    /**
     * 加载指定文件并返回内容
     *
     *     $foo = Base::load('foo.php');
     *
     * @param  string $file
     * @return mixed
     */
    public static function load($file)
    {
        return include $file;
    }

    /**
     * 版本号字符串
     *
     * @return string
     */
    public static function version()
    {
        return 'Tourze ' . self::VERSION;
    }

    /**
     * 获取指定组件
     *
     * @param string $name
     * @return \tourze\Base\Component
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     * @return Component|object
     */
    public static function get($name)
    {
        if ( ! isset(self::$components[$name]))
        {
            // 检查配置表是否有记录
            if ( ! $config = Config::load('main')->get('component.' . $name))
            {
                throw new ComponentNotFoundException('The requested component [:component] not found.', [
                    ':component' => $name,
                ]);
            }
            self::set($name, $config);
        }

        return self::$components[$name];
    }

    /**
     * 保存（或替换）一个组件
     *
     * @param string $name
     * @param array  $config
     * @throws \tourze\Base\Exception\ComponentClassNotFoundException
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function set($name, array $config)
    {
        // 还是没有，那么抛出异常
        if ( ! $config)
        {
            throw new ComponentNotFoundException('The requested component [:component] missing config.', [
                ':component' => $name,
            ]);
        }

        $class = Arr::get($config, 'class');
        if ( ! $class || ! class_exists($class))
        {
            throw new ComponentClassNotFoundException('The requested component class [:class] not found.', [
                ':class' => $class,
            ]);
        }

        /** @var Component $instance */
        $instance = new $class(Arr::get($config, 'params'));
        foreach (Arr::get($config, 'call') as $method => $args)
        {
            call_user_func_array([$instance, $method], $args);
        }

        self::$components[$name] = $instance;
    }

    /**
     * 删除指定的组件
     *
     * @param string $name
     * @return \tourze\Base\Component
     */
    public static function reload($name)
    {
        unset(self::$components[$name]);
        return self::get($name);
    }

    /**
     * 获取HTTP组件
     *
     * @return \tourze\Base\Component\Http
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getHttp()
    {
        return self::get('http');
    }

    /**
     * 获取日志组件
     *
     * @return \tourze\Base\Component\Log
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getLog()
    {
        return self::get('log');
    }

    /**
     * 获取会话组件
     *
     * @return \tourze\Base\Component\Session
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getSession()
    {
        return self::get('session');
    }

    /**
     * 获取Flash组件
     *
     * @return \tourze\Base\Component\Flash
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getFlash()
    {
        return self::get('flash');
    }

    /**
     * 获取缓存组件
     *
     * @return \tourze\Base\Component\Cache
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getCache()
    {
        return self::get('cache');
    }

    /**
     * 获取邮件组件
     *
     * @return \tourze\Base\Component\Mail
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getMail()
    {
        return self::get('mail');
    }

    /**
     * @var string 时区
     */
    public $timezone = 'PRC';

    /**
     * @var string 当前地理位置
     */
    public $locale = 'en_US.utf-8';

    /**
     * @var string 输入输出编码
     */
    public $charset = 'utf-8';

    /**
     * 单次请求的初始化
     *
     * @throws BaseException
     */
    public function init()
    {
        /**
         * 设置默认时区
         */
        date_default_timezone_set($this->timezone);

        /**
         * 设置地区信息（地域信息）
         */
        setlocale(LC_ALL, $this->locale);

        if (function_exists('mb_internal_encoding'))
        {
            mb_internal_encoding($this->charset);
        }

        /**
         * 当输入字符的编码是无效的，或者字符代码不存在于输出的字符编码中时，可以为其指定一个替代字符。
         *
         * @link http://www.php.net/manual/function.mb-substitute-character.php
         */
        mb_substitute_character('none');

        // 确保输入的数据安全
        $_GET = Security::sanitize($_GET);
        $_POST = Security::sanitize($_POST);
        $_COOKIE = Security::sanitize($_COOKIE);

        self::cleanComponents();
    }
}

<?php

namespace tourze\Base;

use Exception;
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
     * @const  string  当前框架版本
     */
    const VERSION = 'beta';

    /**
     * @var string 时区
     */
    public static $timezone = 'PRC';

    /**
     * @var string 当前地理位置
     */
    public static $locale = 'en_US.utf-8';

    /**
     * @var  boolean  是否运行在windows系统下
     */
    public static $isWindows = false;

    /**
     * @var  boolean  是否启用了PHP安全模式
     */
    public static $safeMode = false;

    /**
     * @var  string   默认输出的内容类型
     */
    public static $contentType = 'text/html';

    /**
     * @var  string  输入输出编码
     */
    public static $charset = 'utf-8';

    /**
     * @var string  当前服务器名称
     */
    public static $serverName = '';

    /**
     * @var array 当前实例的主机名列表
     */
    public static $hostNames = [];

    /**
     * @var  string  当前应用所在路径，相对于web根目录
     */
    public static $baseUrl = '/';

    /**
     * @var  string  缺省文件名称
     */
    public static $indexFile = false;

    /**
     * @var array 日志配置信息
     */
    public static $logConfig = null;

    /**
     * @var  string  文件缓存使用的目录
     */
    public static $cacheDir;

    /**
     * @var  int  自带缓存的默认生命周期
     */
    public static $cacheLife = 60;

    /**
     * @var  boolean  自定义X-Powered-By
     */
    public static $expose = false;

    /**
     * @var Component[]
     */
    public static $components = [];

    /**
     * 初始化
     *
     * @throws BaseException
     * @return void
     */
    public function init()
    {
        if ( ! IN_SAE)
        {
            /**
             * unserialization的自动加载，要注意在sae中这个是不支持的
             *
             * @link http://www.php.net/manual/function.spl-autoload-call
             * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
             */
            ini_set('unserialize_callback_func', 'spl_autoload_call');
        }

        /**
         * 当输入字符的编码是无效的，或者字符代码不存在于输出的字符编码中时，可以为其指定一个替代字符。
         *
         * @link http://www.php.net/manual/function.mb-substitute-character.php
         */
        mb_substitute_character('none');

        // Start an output buffer
        ob_start();

        /**
         * 设置默认时区
         */
        date_default_timezone_set(self::$timezone);

        /**
         * 设置地区信息（地域信息）
         */
        setlocale(LC_ALL, self::$locale);

        self::$isWindows = ('\\' === DIRECTORY_SEPARATOR);

        self::$safeMode = (bool) ini_get('safe_mode');

        if (function_exists('mb_internal_encoding'))
        {
            mb_internal_encoding(self::$charset);
        }

        $_GET = Security::sanitize($_GET);
        $_POST = Security::sanitize($_POST);
        $_COOKIE = Security::sanitize($_COOKIE);
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
     * 一个简单的内置缓存类
     *
     *     // 写缓存
     *     self::cache('foo', 'hello, world');
     *     // 读取缓存
     *     $foo = self::cache('foo');
     *
     * @throws  BaseException
     * @param   string $name    缓存名
     * @param   mixed  $data    缓存数据
     * @param   int    $expired 缓存生效时间（单位：秒）
     * @return  mixed|boolean
     */
    public static function cache($name, $data = null, $expired = null)
    {
        $file = sha1($name) . '.txt';
        $dir = self::$cacheDir . DIRECTORY_SEPARATOR . $file[0] . $file[1] . DIRECTORY_SEPARATOR;

        if (null === $expired)
        {
            $expired = self::$cacheLife;
        }

        // 读取数据
        if (null === $data)
        {
            if (is_file($dir . $file))
            {
                $result = unserialize(file_get_contents($dir . $file));
                if (isset($result['data']) && isset($result['expired']))
                {
                    // 过期
                    if (time() <= $result['expired'])
                    {
                        return $result['data'];
                    }
                }
                @unlink($dir . $file);
            }

            // 查找不到内存
            return null;
        }

        // 下面就是保存缓存的逻辑了

        // 自动创建目录
        if ( ! is_dir($dir))
        {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $result = [
            'data'    => $data,
            'expired' => time() + $expired,
        ];
        $result = serialize($result);
        try
        {
            return (bool) file_put_contents($dir . $file, $result, LOCK_EX);
        }
        catch (Exception $e)
        {
            return false;
        }
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
     * 获取日志组件
     *
     * @return \tourze\Base\Component\Session
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getSession()
    {
        return self::get('session');
    }

    /**
     * 获取日志组件
     *
     * @return \tourze\Base\Component\Flash
     * @throws \tourze\Base\Exception\ComponentNotFoundException
     */
    public static function getFlash()
    {
        return self::get('flash');
    }
}

<?php

namespace tourze\Base;

use ErrorException;
use Exception;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;

/**
 * sdk基础类，包含一些最基础和常用的操作：
 *
 * - 环境初始化
 * - 类的自动加载
 * - 助手traits
 *
 * @package    tourze\Base
 * @category   Base
 * @access     public
 * @author     lzp <25803471@qq.com>
 */
class Base extends Object
{

    /**
     * @const  string  当前框架版本
     */
    const VERSION = 'beta';

    /**
     * @const  string  环境配置常量：线上正式
     */
    const PRODUCTION = 10;

    /**
     * @const  string  环境配置常量：预览版本
     */
    const STAGING = 20;

    /**
     * @const  string  环境配置常量：测试版本
     */
    const TESTING = 30;

    /**
     * @const  string  环境配置常量：开发版本
     */
    const DEVELOPMENT = 40;

    /**
     * @const  string  文件缓存的格式
     */
    const FILE_CACHE = ":header \n\n// :name\n\n:data\n";

    /**
     * @var  string  当前的环境配置
     */
    public static $environment = self::DEVELOPMENT;

    /**
     * @var string
     */
    public static $profileEntry = '_debug';

    /**
     * @var bool|mixed  调试密码
     */
    public static $profilePassword = false;

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
     * @var  boolean  魔法引号是否开启
     */
    public static $magicQuotes = false;

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
     * @var  string  当前服务器名称
     */
    public static $serverName = '';

    /**
     * @var  array   list of valid host names for this instance
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
     * @var  integer  自带缓存的默认生命周期
     */
    public static $cacheLife = 60;

    /**
     * @var  boolean  是否缓存[self::findFile]的搜索结果
     */
    public static $cacheActive = false;

    /**
     * @var string
     */
    public static $cacheKey = 'Base::findFile()';

    /**
     * @var  boolean  是否显示错误信息
     */
    public static $errors = true;

    /**
     * @var  boolean  自定义X-Powered-By
     */
    public static $expose = false;

    /**
     * @var  array   当前激活的第三方模块
     */
    protected static $_modules = [];

    /**
     * @var  array   用户查找文件的路径
     */
    protected static $_paths = [
        APPPATH,
        SYSPATH
    ];

    /**
     * @var array  下面这些目录的文件需要完整查找
     */
    protected static $_returnArrayDirectory = [
        'i18n',
        'message',
    ];

    /**
     * @var  array   缓存已经查找过的文件路径
     */
    protected static $_files = [];

    /**
     * @var  boolean  文件缓存已经有了变更
     */
    protected static $_filesChanged = false;

    /**
     * @var  object  SAE缓存
     */
    protected static $_saeMemcache = null;

    /**
     * @var array SDK工作流分层
     */
    public static $layers = [
        'tourze\Base\BaseFlow',  // SDK基础工作层
        'tourze\Http\HttpFlow', // 执行HTTP相关控制
    ];

    /**
     * 初始化
     *
     * @throws  BaseException
     *
     * @return  void
     * @uses    Base::globals
     * @uses    Base::sanitize
     * @uses    Base::cache
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

        /**
         * 当前的应用环境
         */
        if (isset($_SERVER['SDK_ENV']))
        {
            Base::$environment = constant('Base::' . strtoupper($_SERVER['SDK_ENV']));
        }
        // 运行环境配置
        Base::$environment = Base::DEVELOPMENT;

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

        if (ini_get('register_globals'))
        {
            self::globals();
        }

        self::$isWindows = ('\\' === DIRECTORY_SEPARATOR);

        self::$safeMode = (bool) ini_get('safe_mode');

        if (function_exists('mb_internal_encoding'))
        {
            mb_internal_encoding(self::$charset);
        }

        self::$magicQuotes = (version_compare(PHP_VERSION, '5.4') < 0 && get_magic_quotes_gpc());

        $_GET = self::sanitize($_GET);
        $_POST = self::sanitize($_POST);
        $_COOKIE = self::sanitize($_COOKIE);

        if (self::$cacheActive)
        {
            self::$_files = self::cache(self::$cacheKey);
        }
    }

    /**
     * 阻止全局变量
     */
    public static function globals()
    {
        if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
        {
            echo "Global variable overload attack detected! Request aborted.\n";
            exit(1);
        }

        $globalVars = array_keys($GLOBALS);
        // 只对系统自带的全局变量进行过滤
        $globalVars = array_diff($globalVars, [
            '_COOKIE',
            '_ENV',
            '_GET',
            '_FILES',
            '_POST',
            '_REQUEST',
            '_SERVER',
            '_SESSION',
            'GLOBALS',
        ]);

        foreach ($globalVars as $name)
        {
            unset($GLOBALS[$name]);
        }
    }

    /**
     * 标准化过滤输入的数据，主要两个功能：
     *
     * - 安全过滤，对特殊字符进行转义
     * - 对换行符进行格式化
     *
     * @param   mixed $value any variable
     *
     * @return  mixed   sanitized variable
     */
    public static function sanitize($value)
    {
        if (is_array($value) || is_object($value))
        {
            foreach ($value as $key => $val)
            {
                $value[$key] = self::sanitize($val);
            }
        }
        elseif (is_string($value))
        {
            if (true === self::$magicQuotes)
            {
                $value = stripslashes($value);
            }
            if (false !== strpos($value, "\r"))
            {
                $value = str_replace([
                    "\r\n",
                    "\r"
                ], "\n", $value);
            }
        }

        return $value;
    }

    /**
     * 返回当前激活的加载路径列表，包括应用、模块、系统目录的路径
     *
     * @return  array
     */
    public static function includePaths()
    {
        return self::$_paths;
    }

    /**
     * 使用级联文件系统来查找文件
     * 如果搜索的是`config`、`messages`、`i18n`目录，那么系统会查找所有路径并合并
     *
     *     // 返回views/template.php的绝对路径
     *     Base::findFile('views', 'template');
     *
     *     // 返回media/css/style.css的绝对路径
     *     Base::findFile('media', 'css/style', 'css');
     *
     *     // 返回合并后的数组
     *     Base::findFile('config', 'main');
     *
     * @param   string  $dir         目录名
     * @param   string  $file        文件名
     * @param   string  $ext         扩展名
     * @param   boolean $returnArray 是否返回数组
     *
     * @return  array   包含了若干个文件路径的数组
     * @return  string  文件路径
     */
    public static function findFile($dir, $file, $ext = null, $returnArray = false)
    {
        if (null === $ext)
        {
            $ext = EXT;
        }
        elseif ($ext)
        {
            $ext = ".{$ext}";
        }
        else
        {
            $ext = '';
        }

        // 未完整的路径
        $path = $dir . DIRECTORY_SEPARATOR . $file . $ext;
        $cachePrefix = $returnArray ? 'array:' : 'path:';

        if (self::$cacheActive && isset(self::$_files[$cachePrefix.$path]))
        {
            // 返回缓存结果
            return self::$_files[$cachePrefix.$path];
        }

        if ($returnArray || in_array($dir, self::$_returnArrayDirectory))
        {
            // 从底部开始查起来
            $paths = array_reverse(self::$_paths);
            $found = [];
            foreach ($paths as $dir)
            {
                if (is_file($dir . $path))
                {
                    $found[] = $dir . $path;
                }
            }
        }
        else
        {
            $found = false;
            foreach (self::$_paths as $dir)
            {
                if (is_file($dir . $path))
                {
                    // A path has been found
                    $found = $dir . $path;
                    break;
                }
            }
        }

        if (true === self::$cacheActive)
        {
            // Add the path to the cache
            self::$_files[$cachePrefix.$path] = $found;
            // Files have been changed
            self::$_filesChanged = true;
        }

        return $found;
    }

    /**
     * 列出指定目录的所有文件
     *
     *     // 查找所有视图文件
     *     $views = self::listFiles('views');
     *
     * @param   string $directory 目录名
     * @param   array  $paths     要搜索的路径
     *
     * @return  array
     */
    public static function listFiles($directory = null, array $paths = null)
    {
        if (null !== $directory)
        {
            $directory .= DIRECTORY_SEPARATOR;
        }

        if (null === $paths)
        {
            // Use the default paths
            $paths = self::$_paths;
        }

        // Create an array for the files
        $found = [];

        foreach ($paths as $path)
        {
            if (is_dir($path . $directory))
            {
                // Create a new directory iterator
                $dir = new \DirectoryIterator($path . $directory);

                foreach ($dir as $file)
                {
                    // Get the file name
                    $filename = $file->getFilename();

                    if ('.' === $filename[0] or '~' === $filename[strlen($filename) - 1])
                    {
                        // Skip all hidden files and UNIX backup files
                        continue;
                    }

                    // Relative filename is the array key
                    $key = $directory . $filename;

                    if ($file->isDir())
                    {
                        if ($subDir = self::listFiles($key, $paths))
                        {
                            if (isset($found[$key]))
                            {
                                // Append the sub-directory list
                                $found[$key] += $subDir;
                            }
                            else
                            {
                                // Create a new sub-directory list
                                $found[$key] = $subDir;
                            }
                        }
                    }
                    else
                    {
                        if ( ! isset($found[$key]))
                        {
                            // Add new files to the list
                            $found[$key] = realpath($file->getPathName());
                        }
                    }
                }
            }
        }

        // Sort the results alphabetically
        ksort($found);

        return $found;
    }

    /**
     * Loads a file within a totally empty scope and returns the output:
     *     $foo = self::load('foo.php');
     *
     * @param   string $file
     *
     * @return  mixed
     */
    public static function load($file)
    {
        return include $file;
    }

    /**
     * Provides simple file-based caching for strings and arrays:
     *     // Set the "foo" cache
     *     self::cache('foo', 'hello, world');
     *     // Get the "foo" cache
     *     $foo = self::cache('foo');
     * All caches are stored as PHP code, generated with [var_export][ref-var].
     * Caching objects may not work as expected. Storing references or an
     * object or array that has recursion will cause an E_FATAL.
     * The cache directory and default cache lifetime is set by [self::init]
     * [ref-var]: http://php.net/var_export
     *
     * @throws  BaseException
     *
     * @param   string  $name     name of the cache
     * @param   mixed   $data     data to cache
     * @param   integer $lifetime number of seconds the cache is valid for
     *
     * @return  mixed    for getting
     * @return  boolean  for setting
     */
    public static function cache($name, $data = null, $lifetime = null)
    {
        if (IN_SAE)
        {
            return self::_saeCache($name, $data, $lifetime);
        }

        // Cache file is a hash of the name
        $file = sha1($name) . '.txt';

        // Cache directories are split by keys to prevent filesystem overload
        $dir = self::$cacheDir . DIRECTORY_SEPARATOR . $file[0] . $file[1] . DIRECTORY_SEPARATOR;

        if (null === $lifetime)
        {
            // Use the default lifetime
            $lifetime = self::$cacheLife;
        }

        if (null === $data)
        {
            if (is_file($dir . $file))
            {
                if ((time() - filemtime($dir . $file)) < $lifetime)
                {
                    // Return the cache
                    try
                    {
                        return unserialize(file_get_contents($dir . $file));
                    }
                    catch (Exception $e)
                    {
                        // Cache is corrupt, let return happen normally.
                    }
                }
                else
                {
                    try
                    {
                        // Cache has expired
                        unlink($dir . $file);
                    }
                    catch (Exception $e)
                    {
                        // Cache has mostly likely already been deleted,
                        // let return happen normally.
                    }
                }
            }

            // Cache not found
            return null;
        }

        // 自动创建目录
        if ( ! is_dir($dir))
        {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        // Force the data to be a string
        $data = serialize($data);

        try
        {
            // Write the cache
            return (bool) file_put_contents($dir . $file, $data, LOCK_EX);
        }
        catch (Exception $e)
        {
            // Failed to write cache
            return false;
        }
    }

    /**
     * 为SAE设置的内置缓存，使用Memcache来实现
     *
     * @param      $name
     * @param null $data
     * @param null $lifetime
     *
     * @return  mixed
     */
    protected static function _saeCache($name, $data = null, $lifetime = null)
    {
        if ( ! self::$_saeMemcache)
        {
            // 虽说不怎么可能，但还是做下判断
            if ( ! function_exists('memcache_init'))
            {
                function memcache_init()
                {
                    return '';
                }
            }
            self::$_saeMemcache = memcache_init();
        }

        // data为空，就是在获取获取数据咯
        if (null === $data)
        {
            if ( ! function_exists('memcache_get'))
            {
                function memcache_get()
                {
                    return '';
                }
            }
            try
            {
                return memcache_get(self::$_saeMemcache, $name);
            }
            catch (Exception $e)
            {
                return null;
            }
        }
        else
        {
            if ( ! function_exists('memcache_set'))
            {
                function memcache_set()
                {
                    return null;
                }
            }
            // 获取生命周期
            $lifetime = (null === $lifetime) ? self::$cacheLife : (int) $lifetime;

            return (bool) memcache_set(self::$_saeMemcache, $name, $data, 0, $lifetime);
        }
    }

    /**
     * 读取消息文本
     *
     *     // Get "username" from messages/text.php
     *     $username = self::message('text', 'username');
     *
     * @param   string $file    文件名
     * @param   string $path    键名
     * @param   mixed  $default 键名不存在时返回默认值
     *
     * @return  string  message string for the given path
     * @return  array   complete message list, when no path is specified
     */
    public static function message($file, $path = null, $default = null)
    {
        static $messages;

        if ( ! isset($messages[$file]))
        {
            $messages[$file] = [];
            if ($files = self::findFile('message', $file))
            {
                foreach ($files as $f)
                {
                    $messages[$file] = Arr::merge($messages[$file], self::load($f));
                }
            }
        }

        if (null === $path)
        {
            // 返回完整的数组
            return $messages[$file];
        }
        else
        {
            // 返回指定的键名
            return Arr::path($messages[$file], $path, $default);
        }
    }

    /**
     * PHP error handler, converts all errors into ErrorExceptions. This handler
     * respects error_reporting settings.
     *
     * @param      $code
     * @param      $error
     * @param null $file
     * @param null $line
     *
     * @throws ErrorException
     * @return  true
     */
    public static function errorHandler($code, $error, $file = null, $line = null)
    {
        if (error_reporting() & $code)
        {
            // This error is not suppressed by current error reporting settings
            // Convert the error into an ErrorException
            throw new ErrorException($error, $code, 0, $file, $line);
        }

        // Do not execute the PHP error handler
        return true;
    }

    /**
     * 版本号字符串
     *
     * @return string
     */
    public static function version()
    {
        // 伪装成Yaf
        return 'Yaf ' . self::VERSION;
    }

}

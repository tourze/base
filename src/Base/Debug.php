<?php

namespace tourze\Base;

use Symfony\Component\VarDumper\VarDumper;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * 调试类的实现
 *
 * @package tourze\Base
 */
class Debug
{

    /**
     * @var Run
     */
    protected static $_debugger = null;

    /**
     * @var bool 是否激活了内置的调试和错误处理方法
     */
    public static $enabled = false;

    /**
     * @var  array  需要显示出来的错误信息级别
     */
    public static $shutdownErrors = [
        E_PARSE,
        E_ERROR,
        E_USER_ERROR
    ];

    /**
     * @return \Whoops\Run
     */
    public static function debugger()
    {
        return self::$_debugger;
    }

    /**
     * 激活调试器
     */
    public static function enable()
    {
        if (self::$enabled)
        {
            return;
        }

        self::$_debugger = new Run;
        self::$_debugger->pushHandler(new PrettyPageHandler);
        self::$_debugger->register();

        self::$enabled = true;
    }

    /**
     * 返回变量的打印html
     *
     *     // 可以打印多个变量
     *     echo self::vars($foo, $bar, $baz);
     *
     * @return string
     */
    public static function vars()
    {
        if (func_num_args() === 0)
        {
            return null;
        }

        $variables = func_get_args();
        $output = [];
        foreach ($variables as $var)
        {
            $output[] = VarDumper::dump($var);
        }

        return implode("\n", $output);
    }
}

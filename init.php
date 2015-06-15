<?php

use tourze\Base\Config;
use tourze\Base\I18n;
use tourze\Base\Message;
use tourze\View\View;

if ( ! function_exists('__'))
{
    /**
     * 自动翻译函数，使用 [strtr](http://php.net/strtr) 来替换参数
     *
     *    __('Welcome back, :user', [':user' => $username]);
     *
     * @param   string $string 要翻译的文本
     * @param   array  $values 变量数组
     * @param   string $lang   源语言
     * @return  string
     */
    function __($string, array $values = null, $lang = 'en-us')
    {
        if (class_exists('tourze\Base\I18n'))
        {
            if ($lang !== I18n::$lang)
            {
                $string = I18n::get($string);
            }
        }

        return empty($values) ? $string : strtr($string, $values);
    }
}

if ( ! defined('IN_SAE'))
{
    define('IN_SAE', function_exists('sae_debug'));
}

// 指定配置加载目录
Config::addPath(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);

// 语言文件目录
I18n::addPath(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR);

// Message目录
Message::addPath(__DIR__ . DIRECTORY_SEPARATOR . 'message' . DIRECTORY_SEPARATOR);

// 指定视图加载目录
View::addPath(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);

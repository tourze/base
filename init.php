<?php

use tourze\Base\I18n;

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

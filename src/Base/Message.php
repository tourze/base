<?php

namespace tourze\Base;

use tourze\Base\Helper\Arr;

class Message
{

    public static $ext = '.php';

    protected static $_messagePaths = [];

    /**
     * 增加加载路径
     *
     * @param $path
     */
    public static function addPath($path)
    {
        self::$_messagePaths[] = $path;
    }

    /**
     * 读取消息文本
     *
     *     // Get "username" from messages/text.php
     *     $username = Message::load('text', 'username');
     *
     * @param   string $file    文件名
     * @param   string $path    键名
     * @param   mixed  $default 键名不存在时返回默认值
     *
     * @return  string  message string for the given path
     * @return  array   complete message list, when no path is specified
     */
    public static function load($file, $path = null, $default = null)
    {
        static $messages;

        if ( ! isset($messages[$file]))
        {
            $messages[$file] = [];

            $files = [];
            foreach (self::$_messagePaths as $includePath)
            {
                if (is_file($includePath . $file . self::$ext))
                {
                    $files[] = $includePath . $file . self::$ext;
                }
            }

            if ( ! empty($files))
            {
                foreach ($files as $f)
                {
                    $messages[$file] = Arr::merge($messages[$file], Base::load($f));
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

}

<?php

use tourze\Base\Config;
use tourze\Base\I18n;
use tourze\Base\Message;
use tourze\View\View;

// 检测是否在SAE中
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

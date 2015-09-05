# 途者PHP框架-Base组件

途者框架是一个受[Kohana](https://kohanaframework.org/)影响深重的框架。
印象中，我的第一个完整的项目（一个新闻发布系统）是使用Kohana开发的，之后第一次创业使用的也是Kohana。
虽然在之后也大量接触了其他优秀框架/类库，但内心还是对KO感情比较深。

所以我的第一个创业项目，在初始时也是使用Kohana开发。但是在开发过程中，也逐渐感觉到Kohana在一些细节上把握得很不好。

在之后，我整理了自己以前的一些积累，结合自己几年的开发经验，写了途者框架。

途者框架__不是最快的框架，也不是大而全的框架，而是能快速开发的框架__，而`Base组件`是其中的基础部分。

因为一些历史原因，Base组件目前不一定解耦很好，但是以后在大家支持下肯定会越来越好。

## 安装

首先需要下载和安装[composer](https://getcomposer.org/)，具体请查看官网的[Download页面](https://getcomposer.org/download/)

在你的`composer.json`中增加：

    "require": {
        "tourze/base": "^1.0"
    },

或直接执行

    composer require tourze/base:"^1.0"

## 文件结构

* cache 存放一些缓存信息，一般不要往这个目录放东西
* config 存放配置信息
* doc 存放额外的文档信息
* i18n 国际化文件存在目录
* message 消息翻译文件目录
* src 大部分类放在其中
* tests 单元测试
* view  视图文件目录
* bootstrap.php 一些通用操作放在其中

途者的组件，一般都是上面的目录结构，如果没有其中一部分的话，会没对应的文件夹。
例如Base组件没有cache目录，因为没有地方需要用到cache目录。

## 核心类

* [tourze\Base\Base 核心基础类](doc/core/base.md)

## 默认组件

* [tourze\Base\Component\Cache 缓存组件](doc/component/cache.md)
* [tourze\Base\Component\Http HTTP组件](doc/component/http.md)
* [tourze\Base\Component\Log 日志组件](doc/component/log.md)
* [tourze\Base\Component\Session 会话组件](doc/component/session.md)
* [tourze\Base\Component\Flash Flash组件](doc/component/flash.md)
* [tourze\Base\Component\Mail 邮件组件](doc/component/mail.md)

## 助手类

Base组件为其他组件提供很多助手方法，用于快速开发：

* [tourze\Base\Helper\Arr 数组助手类](doc/helper/arr.md)
* [tourze\Base\Helper\Cookie Cookie助手类](doc/helper/cookie.md)
* [tourze\Base\Helper\Date 日期时间助手类](doc/helper/date.md)
* [tourze\Base\Helper\File 文件助手类](doc/helper/file.md)
* [tourze\Base\Helper\Mime MIME助手类](doc/helper/mime.md)
* [tourze\Base\Helper\Text 文本助手类](doc/helper/text.md)
* [tourze\Base\Helper\Url URL助手类](doc/helper/url.md)

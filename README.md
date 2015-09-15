# tourze框架-Base模块

途者框架是一个受[Kohana](https://kohanaframework.org/)影响深重的框架。
印象中，我的第一个完整的项目（一个新闻发布系统）是使用Kohana开发的，之后第一次创业使用的也是Kohana。
虽然在之后也大量接触了其他优秀框架/类库（如Symfony、Yii），但内心还是对KO感情比较深。

所以我的第一个创业项目，在选型时选择了PHP，选择了Kohana来做开发。

在不断使用的过程中，Kohana的一些缺点不断突出。如CFS的加载规则导致大量重命名类的出现，模块与模块之间的依赖关系难解决。
还有就是一直Kohana官方开发组对命名空间的支持一直态度摇摆，迟迟不肯跟随时代步伐。

然后就有了tourze框架。

tourze框架__不是最快的框架，也不是大而全的框架，而是能快速开发的框架__，而`Base模块`是其中的基础部分。

Base模块只包含了一些基础的概念实现和助手类。如果你想要完整实现一个项目，可以看下途者框架其他模块。

因为个人精力有限，Base模块目前不一定解耦很好，但是以后在大家支持下肯定会越来越好。

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

途者每个模块，一般都是上面的目录结构，如果没有其中一部分的话，会没对应的文件夹。

例如Base组件没有cache目录，因为没有地方需要用到cache目录。

> 对于不明白的内容，如果你清楚对应的类是什么，那么强烈建议你先试下读代码，先尝试去思考。
> 因为文档不能尽然描述框架功能，而且描述也可能有偏差，如果你能亲自探索过，那么自然能更加容易理解文档。

## 概念

* [组件的说明和使用](doc/concept/component.md)

## 核心类

* tourze\Base\Base 核心基础类
* tourze\Base\Config 配置加载类

## 默认组件

* tourze\Base\Component\Cache 缓存组件
* tourze\Base\Component\Http HTTP组件
* tourze\Base\Component\Log 日志组件
* tourze\Base\Component\Session 会话组件
* tourze\Base\Component\Flash Flash组件
* tourze\Base\Component\Mail 邮件组件

## 助手类

Base组件为其他组件提供很多助手方法，用于快速开发：

* tourze\Base\Helper\Arr 数组助手类
* tourze\Base\Helper\Cookie Cookie助手类
* tourze\Base\Helper\Date 日期时间助手类
* tourze\Base\Helper\File 文件助手类
* tourze\Base\Helper\Mime MIME助手类
* tourze\Base\Helper\Text 文本助手类
* tourze\Base\Helper\Url URL助手类

## 在NGINX运行

配置文件如下：

    log_format  test.tourze.com  '$remote_addr - $remote_user [$time_local] "$request" '
                 '$status $body_bytes_sent "$http_referer" '
                 '"$http_user_agent" $http_x_forwarded_for';
    server
        {
            listen       80;
            server_name test.tourze.com;
            index index.html index.htm index.php default.html default.htm default.php;
            root  /vagrant/com.tourze.test/web;
    
            include other.conf;
            location ~ .*\.(php|php5)?$
                {
                    try_files $uri =404;
                    fastcgi_pass  unix:/tmp/php-cgi.sock;
                    fastcgi_index index.php;
                    include fcgi.conf;
                }
    
            location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
                {
                    expires      30d;
                }
    
            location ~ .*\.(js|css)?$
                {
                    expires      12h;
                }
    
            if (!-f $request_filename){
                rewrite ^(.*)$ /index.php;
            }
    
            access_log  /vagrant/log/test.tourze.com.log  test.tourze.com;
        }

请根据你的具体环境，对上面的配置项进行调整

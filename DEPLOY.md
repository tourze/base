
途者框架使用composer来管理依赖和自动加载

框架目录说明：

* config    框架配置文件，默认配置信息会放到这里
* i18n      默认的国际化文件
* message   默认的消息对应文本
* __src__   框架主文件
* view      默认视图文件
* init.php  额外自动加载的初始化文件

## 第一步，composer安装框架

这一步很简单，进入项目目录后，首先`composer init`。然后项目目录会多个`composer.json`

然后修改成大概下面的格式：

    {
      "name": "ywisax/demo",
      "description": "description_text",
      "minimum-stability": "stable",
      "license": "proprietary",
      "authors": [
        {
          "name": "YwiSax",
          "email": "25803471@qq.com"
        }
      ],
      "require": {
        "tourze/core": "dev-master"
      },
      "autoload": {
        "psr-0": {
          "demo\\": "src/"
        }
      },
      "repositories": [
        {
          "type": "git",
          "url": "https://git.oschina.net/ywisax/com.tourze.php.core.git"
        },
        {
          "type": "composer",
          "url": "http://php.composer.org/repo/packagist/"
        },
        {"packagist": false}
      ]
    }

然后执行`composer update`即可安装框架。

## 目录规范

为了统一开发规范，我们建议使用以下格式来定义你的目录名：

* __src__  项目自身的文件，一般php的class都放到这里
* config   项目配置文件
* i18n     项目国际化文件
* message  项目的消息翻译文件
* view     视图文件
* vendor   如果你上面步骤没错的话，那么应该有个vendor文件夹在此
* bootstrap.php 一些通用的逻辑放到此处，如家在composer自动加载器，日志配置等等
* web      对外的web目录
    * index.php  php入口文件
    * robots.txt

## nginx配置

目前我们只推荐大家使用nginx + php-fpm来做web容器。参考配置文件如下：

    log_format  captcha.tourze.com  '$remote_addr - $remote_user [$time_local] "$request" '
                 '$status $body_bytes_sent "$http_referer" '
                 '"$http_user_agent" $http_x_forwarded_for';
    server
        {
            listen       80;
            server_name captcha.tourze.com test.captcha.tourze.com local.captcha.tourze.com;
            index index.html index.htm index.php default.html default.htm default.php;
            root  /vagrant/wwwroot/lzp/com.tourze.captcha/web;
    
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
    
            access_log  /vagrant/log/vhost/captcha.tourze.com.log  captcha.tourze.com;
        }

## 最后

如果你有更多疑问，可以随时找途者团队
# 组件

途者框架的组件，其实也是封装了特定功能的模块，只是调用方式上有所不同。

首先我们假设个场景，我们需要为我们的应用增加缓存功能，此时没性能方面的要求，所以可以简单地使用文件缓存来完成：

    $cache = new FileCache;
    $cache->set('key1', 'val1');

系统运行一段时间后，文件缓存因为吃了大部分IO，导致整个应用反应缓慢，此时你也应该想到了，我们可以使用memcached或者redis来做缓存。
所以我们去修改代码：

    //$cache = new FileCache;
    $cache = new RedisCache;
    $cache->connect('localhost');
    $cache->set('key1', 'val1');

再跟着，单redis实例也满足不了缓存要求，我们必须上分布式，于是我们又要改代码...在需求不断变更，代码不断迭代的web开发中，上面的做法是很不科学的。
因为频繁改动这些代码，会导致代码越来越难维护。

所以，在途者框架中，我们将一些常用模块封装成组件，提供最基础的实现。
然后如果要扩展或变更功能，则用更优雅的方式来实现。

上述代码在tourze框架中，可以直接使用：

    Base::get('cache')->set('key2', 'val2');

如果要更换缓存的保存、读取方式，则在`config/main-local.php`中指定对应的组件类即可。

### 使用组件

首先你应该看下`config/main.php`这个配置文件，然后对组件的配置有个大概的认识。

所有组件的配置都应该放在`main`这个配置的`component`节，然后每个组件在配置时，可以进行一些初始化操作。如：

    // 此处的key，就是组件名称
    'sample'    => [
        'class'  => 'app\Component\Sample', // 组件类名
        'params' => [
            'var1' => 1,
        ], // 此处会自动设置对应属性
        'call'   => [
            'callRemote' => ['tcp://127.0.0.1'], // 组件初始化时调用的方法，key为方法名，值为传入的参数列表
        ],
    ],

默认tourze框架包含了：

1. Cache缓存组件，使用`Base::get('cache')`或`Base::getCache()`调用
2. Http组件，使用`Base::get('http')`或`Base::getHttp()`调用
3. Log日志组件，使用`Base::get('log')`或`Base::getLog()`调用
4. Mail邮件组件，使用`Base::get('mail')`或`Base::getMail()`调用
5. Flash消息组件，使用`Base::get('flash')`或`Base::getFlash()`调用
6. Session会话组件，使用`Base::get('session')`或`Base::getSession()`调用

### 创建组件

#### 创建组件类

    <?php
    
    namespace app\Component;
    
    use tourze\Base\Component;
    use tourze\Base\ComponentInterface;
    
    class Sample extends Component implements ComponentInterface
    {
        public function run($person)
        {
            echo "$person is running.\n";
        }
    }

#### 增加组件配置

创建`config/main-local.php`，内容为：

    <?php
    
    return [
        'component' => [
            'sample' => [
                'class'  => 'app\Component\Sample',
            ],
        ],
    ];

#### 调用组件

    <?php
    use tourze\Base\Base;
    // ....
    Base::get('sample')->run();

### 其他说明

此处备注其他使用说明

#### 组件的持久化

在一些场景中，我们可能需要将一些组件实例进行持久化。

例如我们使用[tourze/server](https://github.com/tourze/server)来做Web服务器或其他应用，为了节省频繁连接数据库的开销，我们可能需要将数据库链接实例进行持久化。

> 在传统的LAMP、LNMP套件环境中，要做到此点不太容易，但是在`tourze/server`中一切皆有可能

此时我们可能将组件中的`$persistence`设为`true`，这样tourze框架在初始化时就会自动对要持久化的实例进行额外处理。

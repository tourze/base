<?php

namespace tourze\Route;

use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Url;
use tourze\Base\Object;
use tourze\Route\Exception\RouteNotFoundException;

/**
 * 路由处理类
 *
 * @property  string $identify
 * @property  array  $regex
 * @property  string $uri
 * @package tourze\Route
 */
class Route extends Object implements RouteInterface
{

    /**
     * @const  URI组信息的正则规则
     */
    const REGEX_GROUP = '\(((?:(?>[^()]+)|(?R))*)\)';

    /**
     * @const  <key>的正则匹配规则
     */
    const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

    /**
     * @const  <segment>的匹配正则
     */
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    /**
     * @const  转义正则
     */
    const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|]';

    /**
     * @var  string  路由中使用的默认协议
     */
    public static $defaultProtocol = 'http://';

    /**
     * @var  array  本地主机表
     */
    public static $localHosts = [
        false,
        '',
        'local',
        'localhost'
    ];

    /**
     * @var string  默认命名空间
     */
    public static $defaultNamespace = '\\controller\\';

    /**
     * @var  string  路由中的默认动作
     */
    public static $defaultAction = 'index';

    /**
     * @var  bool  把URI中的参数都转为小写
     */
    public static $lowerUri = false;

    /**
     * @var  array  记录所有路由信息的表
     */
    protected static $_routes = [];

    /**
     * 设置和保存指定的路由信息
     *
     *     Route::set('default', '(<controller>(/<action>(/<id>)))')
     *         ->defaults([
     *             'controller' => 'welcome',
     *         ]);
     *
     * @param string $name  路由名称
     * @param string $uri   URI规则
     * @param array  $regex 匹配规则
     * @param bool   $force
     * @return static
     */
    public static function set($name, $uri = null, $regex = null, $force = false)
    {
        if (isset(self::$_routes[$name]) && ! $force)
        {
            return self::$_routes[$name];
        }
        return self::$_routes[$name] = new Route($uri, $regex, $name);
    }

    /**
     * 强制替换指定路由，如果不存在的话，那就新增
     *
     * @param string $name
     * @param string $uri
     * @param array  $regex
     * @return static
     */
    public static function replace($name, $uri = null, $regex = null)
    {
        return self::set($name, $uri, $regex, true);
    }

    /**
     * 获取指定的路由信息
     *
     *     $route = Route::get('default');
     *
     * @param  string $name 路由名称
     * @return Route
     * @throws BaseException
     */
    public static function get($name)
    {
        if ( ! isset(Route::$_routes[$name]))
        {
            throw new RouteNotFoundException('The requested route does not exist: :route', [
                ':route' => $name
            ]);
        }

        return Route::$_routes[$name];
    }

    /**
     * 检测指定路由是否存在
     *
     * @param string $name
     * @return bool
     */
    public static function exists($name)
    {
        return isset(self::$_routes[$name]);
    }

    /**
     * 获取所有已经定义的路由信息
     *
     *     $routes = Route::all();
     *
     * @return  array
     */
    public static function all()
    {
        return Route::$_routes;
    }

    /**
     * 根据指定的路由返回URL，等同于下面的代码：
     *
     *     echo URL::site(Route::get($name)->uri($params), $protocol);
     *
     * @param  string $name     路由名
     * @param  array  $params   URI参数
     * @param  mixed  $protocol 协议字符串、布尔值、等等
     * @return string
     */
    public static function url($name, array $params = null, $protocol = null)
    {
        $route = Route::get($name);
        return $route->isExternal()
            ? $route->uri($params)
            : Url::site($route->uri($params), $protocol);
    }

    /**
     * 解析和返回路由规则的参数
     *
     *     $compiled = Route::compile('<controller>(/<action>(/<id>))', [
     *         'controller' => '[a-z]+',
     *         'id' => '\d+',
     *     ]);
     *
     * @param  string $uri
     * @param  array  $regex
     * @return string
     */
    public static function compile($uri, array $regex = null)
    {
        // The URI should be considered literal except for keys and optional parts
        // Escape everything preg_quote would escape except for : ( ) < >
        $expression = preg_replace('#' . Route::REGEX_ESCAPE . '#', '\\\\$0', $uri);

        if (false !== strpos($expression, '('))
        {
            // Make optional parts of the URI non-capturing and optional
            $expression = str_replace(['(', ')'], ['(?:', ')?'], $expression);
        }

        // 插入默认规则
        $expression = str_replace(['<', '>'], ['(?P<', '>' . Route::REGEX_SEGMENT . ')'], $expression);

        if ($regex)
        {
            $search = $replace = [];
            foreach ($regex as $key => $value)
            {
                $search[] = "<$key>" . Route::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            // Replace the default regex with the user-specified regex
            $expression = str_replace($search, $replace, $expression);
        }

        return '#^' . $expression . '$#uD';
    }

    /**
     * @var string  当前路由对象的标示符
     */
    protected $_identify = '';

    /**
     * @var  array  额外执行的filter
     */
    protected $_filters = [];

    /**
     * @var string  当前URI
     */
    protected $_uri = '';

    /**
     * @var array
     */
    protected $_regex = [];

    /**
     * @var array 默认参数
     */
    protected $_defaults = [
        'method' => false,
        'action' => 'index',
        'host'   => false
    ];

    /**
     * @var  string
     */
    protected $_routeRegex;

    /**
     * 创建一条新的路由记录
     *
     *     $route = new Route($uri, $regex);
     *
     * @param string $uri      URI
     * @param array  $regex    规则描述
     * @param string $identify 路由名称
     */
    public function __construct($uri = null, $regex = null, $identify = null)
    {
        parent::__construct();

        if (null === $uri)
        {
            return;
        }

        if ( ! empty($uri))
        {
            $this->_uri = $uri;
        }
        if ( ! empty($regex))
        {
            $this->_regex = $regex;
        }
        if ( ! empty($identify))
        {
            $this->identify = $identify;
        }

        $this->_routeRegex = self::compile($uri, $regex);
    }

    /**
     * 获取当前路由的名称
     *
     *     $name = $route->name()
     *
     * @param  Route $route 指定的路由实例
     * @return string
     */
    public function name(Route $route = null)
    {
        if (null === $route)
        {
            $route = $this;
        }

        return array_search($route, self::$_routes);
    }

    /**
     * 设置或读取路由规则的默认参数
     *
     *     $route->defaults([
     *         'controller' => 'welcome',
     *         'action'     => 'index'
     *     ]);
     *
     * @param   array $defaults 键值数据
     * @return  $this|array
     */
    public function defaults(array $defaults = null)
    {
        if (null === $defaults)
        {
            return $this->_defaults;
        }
        $this->_defaults = $defaults;

        return $this;
    }

    /**
     * filter会在路由参数被返回前执行：
     *
     *     $route->filter(
     *         function(Route $route, $params, Request $request)
     *         {
     *             if (Request::POST !== $request->method())
     *             {
     *                 return false;
     *             }
     *             if ($params and 'welcome' === $params['controller'])
     *             {
     *                 $params['controller'] = 'home';
     *             }
     *             return $params;
     *         }
     *     );
     *
     * 如果要跳过当前匹配规则，可以返回false。
     * 如果要更改当前路由参数，返回修改后的参数数组即可。
     *
     * [!!] 在filter被调用前，默认数据就已经被合并到路由参数中的了
     *
     * @throws  BaseException
     * @param   callable $callback 回调函数，可以为字符串、数组或closure
     * @return  $this
     */
    public function filter($callback)
    {
        if ( ! is_callable($callback))
        {
            throw new BaseException('Invalid Route::callback specified');
        }

        $this->_filters[] = $callback;

        return $this;
    }

    /**
     * 检测路由是否与路由表中的记录有匹配
     *
     * @param string $uri    URI
     * @param string $method URI的请求方法
     * @return array
     */
    public function matches($uri, $method = null)
    {
        $uri = trim($uri, '/');

        // 先校验URI是否正确
        if ( ! preg_match($this->_routeRegex, $uri, $matches))
        {
            return false;
        }

        // 解析参数
        $params = [];
        foreach ($matches as $key => $value)
        {
            if (is_int($key))
            {
                // 如果键值不是关联的话，那么就跳过
                continue;
            }
            $params[$key] = $value;
        }

        // 设置默认参数
        foreach ($this->_defaults as $key => $value)
        {
            if ( ! isset($params[$key]) || '' === $params[$key])
            {
                // 如果没匹配到，那么就设置默认值
                $params[$key] = $value;
            }
        }

        // 处理method
        if (isset($params['method']) && $params['method'])
        {
            if (is_array($params['method']))
            {
                if ( ! in_array($method, $params['method']))
                {
                    return false;
                }
            }
            else
            {
                if ($params['method'] != $method)
                {
                    return false;
                }
            }
        }

        if ( ! empty($params['controller']))
        {
            $params['controller'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['controller'])));
        }

        if ( ! empty($params['directory']))
        {
            $params['directory'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $params['directory'])));
        }

        if ($this->_filters)
        {
            foreach ($this->_filters as $callback)
            {
                // 执行过滤器
                $return = call_user_func($callback, $this, $params, $uri);

                if (false === $return)
                {
                    // 停止继续匹配
                    return false;
                }
                elseif (is_array($return))
                {
                    // 修改参数值
                    $params = $return;
                }
            }
        }

        return $params;
    }

    /**
     * 是否是外部链接
     *
     * @return  boolean
     */
    public function isExternal()
    {
        return ! in_array(Arr::get($this->_defaults, 'host', false), Route::$localHosts);
    }

    /**
     * 传入参数，生成当前路由的uri
     *
     * @param  array $params URI参数
     * @return string
     * @throws BaseException
     */
    public function uri(array $params = null)
    {
        $defaults = $this->_defaults;

        if (self::$lowerUri)
        {
            if (isset($params['controller']))
            {
                $params['controller'] = strtolower($params['controller']);
            }
            if (isset($params['directory']))
            {
                $params['directory'] = strtolower($params['directory']);
            }
        }

        /**
         * 匿名函数，用于循环替换路由参数
         *
         * @param  string  $portion  URI定义部分
         * @param  boolean $required 参数是否必须的
         * @return array 返回保存参数的数组
         * @throws BaseException
         */
        $compile = function ($portion, $required) use (&$compile, $defaults, $params)
        {
            $missing = [];

            $pattern = '#(?:' . Route::REGEX_KEY . '|' . Route::REGEX_GROUP . ')#';
            $result = preg_replace_callback($pattern, function ($matches) use (&$compile, $defaults, &$missing, $params, &$required)
            {
                if ('<' === $matches[0][0])
                {
                    $param = $matches[1];

                    if (isset($params[$param]))
                    {
                        $required = ($required || ! isset($defaults[$param]) || $params[$param] !== $defaults[$param]);
                        return $params[$param];
                    }

                    // 直接返回参数默认值
                    if (isset($defaults[$param]))
                    {
                        return $defaults[$param];
                    }

                    $missing[] = $param;
                }
                else
                {
                    $result = $compile($matches[2], false);

                    if ($result[1])
                    {
                        $required = true;
                        return $result[0];
                    }
                }

                return null;
            }, $portion);

            if ($required && $missing)
            {
                throw new BaseException('Required route parameter not passed: :param', [
                    ':param' => reset($missing)
                ]);
            }

            return [
                $result,
                $required
            ];
        };

        list($uri) = $compile($this->_uri, true);

        // 过滤URI中的重复斜杆
        $uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

        // 如果是外部链接
        if ($this->isExternal())
        {
            $host = $this->_defaults['host'];

            // 使用默认协议
            if (false === strpos($host, '://'))
            {
                $host = Route::$defaultProtocol . $host;
            }

            $uri = rtrim($host, '/') . '/' . $uri;
        }

        return $uri;
    }

    /**
     * 获取标示符
     *
     * @return string
     */
    public function getIdentify()
    {
        return $this->_identify;
    }

    /**
     * 设置标示符
     *
     * @param string $identify
     */
    public function setIdentify($identify)
    {
        $this->_identify = $identify;
    }

    /**
     * @return array
     */
    public function getRegex()
    {
        return $this->_regex;
    }

    /**
     * @param array $regex
     */
    public function setRegex($regex)
    {
        $this->_regex = $regex;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;
    }

}

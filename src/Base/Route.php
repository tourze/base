<?php

namespace tourze\Base;

use tourze\Base\Exception\BaseException;
use tourze\Base\Exception\RouteNotFoundException;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Url;

/**
 * 路由处理类
 *
 * @property   string  $identify
 * @package    Base
 * @category   Base
 * @author     YwiSax
 */
class Route extends Object
{

    /**
     * @const  URI组信息的正则规则
     */
    const REGEX_GROUP = '\(((?:(?>[^()]+)|(?R))*)\)';

    /**
     * @const  <segment>的正则匹配规则
     */
    const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

    /**
     * @const  What can be part of a <segment> value
     */
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    /**
     * @const  What must be escaped in the route regex
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
     * @param   string $name  路由名称
     * @param   string $uri   URI规则
     * @param   array  $regex 匹配规则
     *
     * @return  Route
     */
    public static function set($name, $uri = null, $regex = null)
    {
        return Route::$_routes[$name] = new Route($uri, $regex, $name);
    }

    /**
     * 获取指定的路由信息
     *
     *     $route = Route::get('default');
     *
     * @param   string $name 路由名称
     *
     * @return  Route
     * @throws  BaseException
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
     * @param   string $name     路由名
     * @param   array  $params   URI参数
     * @param   mixed  $protocol 协议字符串、布尔值、等等
     *
     * @return  string
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
     * @param   string $uri
     * @param   array  $regex
     *
     * @return  string
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

        // Insert default regex for keys
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
     * @var  array  route filters
     */
    protected $_filters = [];

    /**
     * @var  string  route URI
     */
    protected $_uri = '';

    /**
     * @var  array
     */
    protected $_regex = [];

    /**
     * @var  array
     */
    protected $_defaults = [
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
     * The $uri parameter should be a string for basic regex matching.
     *
     * @param   string $uri   URI
     * @param   array  $regex 规则描述
     * @param  string  $identify 路由名称
     */
    public function __construct($uri = null, $regex = null, $identify = null)
    {
        if (null === $uri)
        {
            // Assume the route is from cache
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
     * @param   Route $route 指定的路由实例
     *
     * @return  string
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
     * Provides default values for keys when they are not present. The default
     * action will always be "index" unless it is overloaded here.
     *
     *     $route->defaults([
     *         'controller' => 'welcome',
     *         'action'     => 'index'
     *     ]);
     *
     * If no parameter is passed, this method will act as a getter.
     *
     * @param   array $defaults key values
     *
     * @return  $this or array
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
     * Filters to be run before route parameters are returned:
     *
     *     $route->filter(
     *         function(Route $route, $params, Request $request)
     *         {
     *             if (Request::POST !== $request->method())
     *             {
     *                 return false; // This route only matches POST requests
     *             }
     *             if ($params and 'welcome' === $params['controller'])
     *             {
     *                 $params['controller'] = 'home';
     *             }
     *             return $params;
     *         }
     *     );
     *
     * To prevent a route from matching, return `false`. To replace the route
     * parameters, return an array.
     *
     * [!!] Default parameters are added before filters are called!
     *
     * @throws  BaseException
     *
     * @param   callable $callback callback string, array, or closure
     *
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
     * @param   string $uri
     * @param null     $method
     * @return array on success
     */
    public function matches($uri, $method = null)
    {
        $uri = trim($uri, '/');

        if ( ! preg_match($this->_routeRegex, $uri, $matches))
        {
            return false;
        }

        $params = [];
        foreach ($matches as $key => $value)
        {
            if (is_int($key))
            {
                // Skip all unnamed keys
                continue;
            }

            // Set the value for all matched keys
            $params[$key] = $value;
        }

        foreach ($this->_defaults as $key => $value)
        {
            if ( ! isset($params[$key]) || '' === $params[$key])
            {
                // Set default values for any key that was not matched
                $params[$key] = $value;
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
                // Execute the filter giving it the route, params, and request
                $return = call_user_func($callback, $this, $params, $uri);

                if (false === $return)
                {
                    // Filter has aborted the match
                    return false;
                }
                elseif (is_array($return))
                {
                    // Filter has modified the parameters
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
     * Generates a URI for the current route based on the parameters given.
     *
     *     // Using the "default" route: "users/profile/10"
     *     $route->uri([
     *         'controller' => 'users',
     *         'action'     => 'profile',
     *         'id'         => '10'
     *     ]);
     *
     * @param   array $params URI parameters
     * @return  string
     * @throws  BaseException
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
         * Recursively compiles a portion of a URI specification by replacing
         * the specified parameters and any optional parameters that are needed.
         *
         * @param   string  $portion  Part of the URI specification
         * @param   boolean $required Whether or not parameters are required (initially)
         *
         * @return  array Tuple of the compiled portion and whether or not it contained specified parameters
         * @throws  BaseException
         */
        $compile = function ($portion, $required) use (&$compile, $defaults, $params)
        {
            $missing = [];

            $pattern = '#(?:' . Route::REGEX_KEY . '|' . Route::REGEX_GROUP . ')#';
            $result = preg_replace_callback($pattern, function ($matches) use (&$compile, $defaults, &$missing, $params, &$required)
            {
                if ('<' === $matches[0][0])
                {
                    // Parameter, unwrapped
                    $param = $matches[1];

                    if (isset($params[$param]))
                    {
                        // This portion is required when a specified
                        // parameter does not match the default
                        $required = ($required || ! isset($defaults[$param]) || $params[$param] !== $defaults[$param]);

                        // Add specified parameter to this result
                        return $params[$param];
                    }

                    // Add default parameter to this result
                    if (isset($defaults[$param]))
                    {
                        return $defaults[$param];
                    }

                    // This portion is missing a parameter
                    $missing[] = $param;
                }
                else
                {
                    // Group, unwrapped
                    $result = $compile($matches[2], false);

                    if ($result[1])
                    {
                        // This portion is required when it contains a group
                        // that is required
                        $required = true;

                        // Add required groups to this result
                        return $result[0];
                    }

                    // Do not add optional groups to this result
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

        // Trim all extra slashes from the URI
        $uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

        if ($this->isExternal())
        {
            // Need to add the host to the URI
            $host = $this->_defaults['host'];
            if (false === strpos($host, '://'))
            {
                // Use the default defined protocol
                $host = Route::$defaultProtocol . $host;
            }
            // Clean up the host and prepend it to the URI
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

}

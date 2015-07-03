<?php

namespace tourze\Http;

use tourze\Base\Object;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Url;
use tourze\Base\Base;
use tourze\Http\Exception\Http404Exception;
use tourze\Http\Exception\HttpException;
use tourze\Http\Request\Client\ExternalClient;
use tourze\Http\Request\Client\InternalClient;
use tourze\Http\Request\Exception\RequestException;
use tourze\Http\Request\RequestClient;
use tourze\Route\Route;
use tourze\Base\Security\Valid;

/**
 * Request. Uses the [Route] class to determine what
 * [Controller] to send the request to.
 *
 * @property   Header    header
 * @property   RequestClient client
 * @property   Route         route
 * @property   array         routes
 * @property   string        body
 * @property   string        controller
 * @property   string        action
 * @property   string        directory
 * @property   string        method
 * @property   string        protocol
 * @property   boolean       secure
 * @property   string        uri
 * @property   string        referrer
 * @property   boolean       external
 * @property   string        requestedWith
 * @package    Base
 * @category   Base
 */
class Request extends Object
{

    // HTTP方法列表
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE   = 'TRACE';
    const CONNECT = 'CONNECT';

    /**
     * @var  string  client user agent
     */
    public static $userAgent = '';

    /**
     * @var  string  client IP address
     */
    public static $clientIp = '0.0.0.0';

    /**
     * @var  string  trusted proxy server IPs
     */
    public static $trustedProxies = [
        '127.0.0.1',
        'localhost',
    ];

    /**
     * @var  Request  main request instance
     */
    public static $initial;

    /**
     * @var  Request  currently executing request instance
     */
    public static $current;

    /**
     * Creates a new request object for the given URI. New requests should be
     * created using the [Request::instance] or [Request::factory] methods.
     *     $request = Request::factory($uri);
     * If $cache parameter is set, the response for the request will attempt to
     * be retrieved from the cache.
     *
     * @param   bool|string $uri            URI of the request
     * @param   array       $clientParams   An array of params to pass to the request client
     * @param   array       $injectedRoutes An array of routes to use, for testing
     * @return  Request|void
     * @throws  BaseException
     */
    public static function factory($uri = true, $clientParams = [], $injectedRoutes = [])
    {
        $request = new Request($uri, $clientParams, $injectedRoutes);

        return $request;
    }

    /**
     * Return the currently executing request. This is changed to the current
     * request when [Request::execute] is called and restored when the request
     * is completed.
     *
     *     $request = Request::current();
     *
     * @return  Request
     */
    public static function current()
    {
        return Request::$current;
    }

    /**
     * Returns the first request encountered by this framework. This will should
     * only be set once during the first [Request::factory] invocation.
     *     // Get the first request
     *     $request = Request::initial();
     *     // Test whether the current request is the first request
     *     if (Request::initial() === Request::current())
     *          // Do something useful
     *
     * @return  Request
     */
    public static function initial()
    {
        return Request::$initial;
    }

    /**
     * Process a request to find a matching route
     *
     * @param   Request|object $request Request
     * @param   array              $routes  Route
     * @return  array
     */
    public static function process(Request $request, $routes = null)
    {
        // Load routes
        $routes = (empty($routes)) ? Route::all() : $routes;
        $params = null;

        foreach ($routes as $name => $route)
        {
            /* @var $route Route */
            // We found something suitable
            if ($params = $route->matches($request->uri))
            {
                return [
                    'params' => $params,
                    'route'  => $route,
                ];
            }
        }

        return null;
    }

    /**
     * Parses an accept header and returns an array (type => quality) of the
     * accepted types, ordered by quality.
     *     $accept = Request::_parseAccept($header, $defaults);
     *
     * @param   string $header  Header to parse
     * @param   array  $accepts Default values
     * @return  array
     */
    protected static function _parseAccept(& $header, array $accepts = null)
    {
        if ( ! empty($header))
        {
            // Get all of the types
            $types = explode(',', $header);

            foreach ($types as $type)
            {
                // Split the type into parts
                $parts = explode(';', $type);

                // Make the type only the MIME
                $type = trim(array_shift($parts));

                // Default quality is 1.0
                $quality = 1.0;

                foreach ($parts as $part)
                {
                    // Prevent undefined $value notice below
                    if (false === strpos($part, '='))
                    {
                        continue;
                    }

                    // Separate the key and value
                    list ($key, $value) = explode('=', trim($part));

                    if ('q' === $key)
                    {
                        // There is a quality for this type
                        $quality = (float) trim($value);
                    }
                }

                // Add the accept type and quality
                $accepts[$type] = $quality;
            }
        }

        // Make sure that accepts is an array
        $accepts = (array) $accepts;

        // Order by quality
        arsort($accepts);

        return $accepts;
    }

    /**
     * @var  string  the x-requested-with header which most likely will be xmlhttprequest
     */
    protected $_requestedWith;

    /**
     * @return RequestClient
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param RequestClient $client
     */
    public function setClient($client)
    {
        $this->_client = $client;
    }

    protected function getRequestedWith()
    {
        return $this->_requestedWith;
    }

    protected function setRequestedWith($requestWith)
    {
        $this->_requestedWith = $requestWith;
    }

    /**
     * @var  string  method: GET, POST, PUT, DELETE, HEAD, etc
     */
    protected $_method = 'GET';

    protected function getMethod()
    {
        return $this->_method;
    }

    protected function setMethod($method)
    {
        $this->_method = strtoupper($method);
    }

    /**
     * @var  string  protocol: HTTP/1.1, FTP, CLI, etc
     */
    protected $_protocol;

    protected function getProtocol()
    {
        if ($this->_protocol)
        {
            return $this->_protocol;
        }
        else
        {
            return $this->_protocol = Http::$protocol;
        }
    }

    protected function setProtocol($protocol)
    {
        $this->_protocol = strtoupper($protocol);
    }

    /**
     * @var  boolean  当前请求是否为安全连接
     */
    protected $_secure = false;

    protected function getSecure()
    {
        return $this->_secure;
    }

    protected function setSecure($secure)
    {
        $this->_secure = (bool) $secure;
    }

    /**
     * @var  string  referring URL
     */
    protected $_referrer;

    protected function getReferrer()
    {
        return $this->_referrer;
    }

    protected function setReferrer($referrer)
    {
        $this->_referrer = (string) $referrer;
    }

    /**
     * @var  Route       route matched for this request
     */
    protected $_route;

    protected function getRoute()
    {
        return $this->_route;
    }

    protected function setRoute(Route $route)
    {
        $this->_route = $route;
    }

    /**
     * @var  Route       array of routes to manually look at instead of the global namespace
     */
    protected $_routes;

    protected function getRoutes()
    {
        return $this->_routes;
    }

    protected function setRoutes($routes)
    {
        $this->_routes = $routes;
    }

    /**
     * @var  Header  headers to sent as part of the request
     */
    protected $_header;

    protected function getHeader()
    {
        return $this->_header;
    }

    protected function setHeader($header)
    {
        $this->_header = $header;
    }

    /**
     * @var  string the body
     */
    protected $_body;

    protected function getBody()
    {
        return $this->_body;
    }

    protected function setBody($content)
    {
        $this->_body = $content;
    }

    /**
     * @var  string  控制器子目录
     */
    protected $_directory = '';

    protected function getDirectory()
    {
        return $this->_directory;
    }

    protected function setDirectory($directory)
    {
        $this->_directory = (string) $directory;
    }

    /**
     * @var  string  当前请求要执行的控制器
     */
    protected $_controller;

    protected function getController()
    {
        return $this->_controller;
    }

    protected function setController($controller)
    {
        $this->_controller = (string) $controller;
    }

    /**
     * @var  string  控制器要执行的动作
     */
    protected $_action;

    protected function getAction()
    {
        return $this->_action;
    }

    protected function setAction($action)
    {
        $this->_action = (string) $action;
    }

    /**
     * @var  string  当前请求的URI
     */
    protected $_uri;

    protected function getUri()
    {
        return empty($this->_uri) ? '/' : $this->_uri;
    }

    protected function setUri($uri)
    {
        $this->_uri = $uri;
    }

    /**
     * @var  boolean  external request
     */
    protected $_external = false;

    protected function getExternal()
    {
        return $this->_external;
    }

    protected function setExternal($external)
    {
        $this->_external = (bool) $external;
    }

    /**
     * @var  array   parameters from the route
     */
    protected $_params = [];

    /**
     * @var array    query parameters
     */
    protected $_get = [];

    /**
     * @var array    post parameters
     */
    protected $_post = [];

    /**
     * @var array    cookies to send with the request
     */
    protected $_cookies = [];

    /**
     * @var RequestClient
     */
    protected $_client;

    protected $_initial = false;

    /**
     * Creates a new request object for the given URI. New requests should be
     * created using the [Request::instance] or [Request::factory] methods.
     *     $request = new Request($uri);
     * If $cache parameter is set, the response for the request will attempt to
     * be retrieved from the cache.
     *
     * @param   string $uri            URI of the request
     * @param   array  $clientParams   Array of params to pass to the request client
     * @param   array  $injectedRoutes An array of routes to use, for testing
     *
     * @throws  RequestException
     */
    public function __construct($uri, $clientParams = [], $injectedRoutes = [])
    {
        $clientParams = is_array($clientParams) ? $clientParams : [];

        // Initialise the header
        $this->header = new Header([]);

        // Assign injected routes
        $this->routes = $injectedRoutes;

        // Cleanse query parameters from URI (faster that parse_url())
        $splitUri = explode('?', $uri);
        $uri = array_shift($splitUri);

        // Initial request has global $_GET already applied
        if (null !== Request::$initial)
        {
            if ($splitUri)
            {
                parse_str($splitUri[0], $this->_get);
            }
        }

        // 要区分内部链接和外部链接
        if (Valid::url($uri))
        {
            // 为其创建一个路由
            $this->route = new Route($uri);
            // Store the URI
            $this->uri = $uri;

            // Set the security setting if required
            if (0 === strpos($uri, 'https://'))
            {
                $this->secure = true;
            }

            $this->external = true;
            // Setup the client
            $this->client = ExternalClient::factory($clientParams);
        }
        else
        {
            // Remove trailing slashes from the URI
            $this->uri = trim($uri, '/');
            // Apply the client
            $this->client = new InternalClient($clientParams);
        }

        parent::__construct();
    }

    /**
     * 初始化
     */
    public function init()
    {
        if ( ! self::$initial)
        {
            self::$initial = $this;
            $this->_initial = true;
        }
    }

    /**
     * Returns the response as the string representation of a request.
     *     echo $request;
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Create a URL string from the current request. This is a shortcut for:
     *     echo URL::site($this->request->uri(), $protocol);
     *
     * @param    mixed $protocol protocol string or Request object
     *
     * @return   string
     * @uses     URL::site
     */
    public function url($protocol = null)
    {
        // Create a URI with the current route and convert it to a URL
        return Url::site($this->uri, $protocol);
    }

    /**
     * Retrieves a value from the route parameters.
     *     $id = $request->param('id');
     *
     * @param   string $key     Key of the value
     * @param   mixed  $default Default value if the key is not set
     *
     * @return  mixed
     */
    public function param($key = null, $default = null)
    {
        if (null === $key)
        {
            // Return the full array
            return $this->_params;
        }

        return isset($this->_params[$key]) ? $this->_params[$key] : $default;
    }

    /**
     * Provides access to the [RequestClient].
     *
     * @param   RequestClient $client
     *
     * @return  RequestClient
     */
    public function client(RequestClient $client = null)
    {
        if (null === $client)
        {
            return $this->_client;
        }
        else
        {
            $this->_client = $client;

            return $this;
        }
    }

    /**
     * Gets and sets the requested with property, which should
     * be relative to the x-requested-with pseudo header.
     *
     * @param   string $requestedWith Requested with value
     *
     * @return  mixed
     */
    public function requestedWith($requestedWith = null)
    {
        if (null === $requestedWith)
        {
            // Act as a getter
            return $this->_requestedWith;
        }

        // Act as a setter
        $this->_requestedWith = strtolower($requestedWith);

        return $this;
    }

    /**
     * Processes the request, executing the controller action that handles this
     * request, determined by the [Route].
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     * By default, the output from the controller is captured and returned, and
     * no headers are sent.
     *     $request->execute();
     *
     * @return \tourze\Http\Response
     * @throws \tourze\Http\Exception\HttpException
     * @throws \tourze\Http\Request\Exception\ClientRecursionException
     * @throws \tourze\Http\Request\Exception\RequestException
     */
    public function execute()
    {
        if ( ! $this->external)
        {
            $processed = Request::process($this, $this->routes);

            if ($processed)
            {
                // 保存匹配到的路由
                $this->route = $processed['route'];
                $params = $processed['params'];

                // 是否为外部链接
                $this->external = $this->route->isExternal();

                if (isset($params['directory']))
                {
                    // Controllers are in a sub-directory
                    $this->directory = $params['directory'];
                }

                // 附加上命名空间
                if (isset($params['namespace']))
                {
                    $this->directory = $params['namespace'];
                }
                if ( ! $this->directory)
                {
                    $this->directory = Route::$defaultNamespace;
                }

                // 修正命名空间
                if (false === strpos($this->directory, '\\'))
                {
                    $this->directory = Route::$defaultNamespace . $this->directory . '\\';
                }

                // 保存控制器
                $this->controller = $params['controller'];

                // 保存动作
                $this->action = (isset($params['action']))
                    ? $params['action']
                    : Route::$defaultAction;

                // These are accessible as public vars and can be overloaded
                unset($params['controller'], $params['action'], $params['directory']);

                // Params cannot be changed once matched
                $this->_params = $params;
            }
        }

        if ( ! $this->route instanceof Route)
        {
            $e = HttpException::factory(404, 'Unable to find a route to match the URI: :uri', [
                ':uri' => $this->uri,
            ]);
            $e->request($this);

            throw $e;
        }

        if ( ! $this->_client instanceof RequestClient)
        {
            throw new RequestException('Unable to execute :uri without a RequestClient', [
                ':uri' => $this->uri,
            ]);
        }

        return $this->_client->execute($this);
    }

    /**
     * 当前请求实例是否为初始实例
     *
     * @return  boolean
     */
    public function isInitial()
    {
        return $this->_initial;
    }

    /**
     * Returns whether this is an ajax request (as used by JS frameworks)
     *
     * @return  boolean
     */
    public function isAjax()
    {
        return ('xmlhttprequest' === $this->requestedWith);
    }

    /**
     * Gets or sets HTTP headers oo the request. All headers
     * are included immediately after the HTTP protocol definition during
     * transmission. This method provides a simple array or key/value
     * interface to the headers.
     *
     * @param   mixed  $key   Key or array of key/value pairs to set
     * @param   string $value Value to set to the supplied key
     *
     * @return  mixed|$this
     */
    public function headers($key = null, $value = null)
    {
        if ($key instanceof Header)
        {
            // Act a setter, replace all headers
            $this->header = $key;

            return $this;
        }

        if (is_array($key))
        {
            // Act as a setter, replace all headers
            $this->header->exchangeArray($key);
            return $this;
        }

        // 自动加载header信息
        if ($this->header->count() === 0 && $this->isInitial())
        {
            $this->header = Http::requestHeaders();
        }

        if (null === $key)
        {
            // Act as a getter, return all headers
            return $this->header;
        }
        elseif (null === $value)
        {
            // Act as a getter, single header
            return ($this->header->offsetExists($key)) ? $this->header->offsetGet($key) : null;
        }

        // Act as a setter for a single header
        $this->header[$key] = $value;

        return $this;
    }

    /**
     * Set and get cookies values for this request.
     *
     * @param   mixed  $key   CookieHelper name, or array of cookie values
     * @param   string $value Value to set to cookie
     *
     * @return  string
     * @return  mixed
     */
    public function cookie($key = null, $value = null)
    {
        if (is_array($key))
        {
            // Act as a setter, replace all cookies
            $this->_cookies = $key;

            return $this;
        }
        elseif (null === $key)
        {
            // Act as a getter, all cookies
            return $this->_cookies;
        }
        elseif (null === $value)
        {
            // Act as a getting, single cookie
            return isset($this->_cookies[$key]) ? $this->_cookies[$key] : null;
        }

        // Act as a setter for a single cookie
        $this->_cookies[$key] = (string) $value;

        return $this;
    }

    /**
     * Returns the length of the body for use with
     * content header
     *
     * @return  integer
     */
    public function content_length()
    {
        return strlen($this->body);
    }

    /**
     * 渲染请求，保存：协议、头部、内容主体
     *
     * @return  string
     */
    public function render()
    {
        if ( ! $post = $this->post())
        {
            $body = $this->body;
        }
        else
        {
            $this->headers('content-type', 'application/x-www-form-urlencoded; charset=' . Base::$charset);
            $body = http_build_query($post, null, '&');
        }

        // Set the content length
        $this->headers('content-length', (string) $this->content_length());

        if (Base::$expose)
        {
            $this->headers('user-agent', Base::version());
        }

        // Prepare cookies
        if ($this->_cookies)
        {
            $cookieString = [];

            // Parse each
            foreach ($this->_cookies as $key => $value)
            {
                $cookieString[] = $key . '=' . $value;
            }

            // Create the cookie string
            $this->header['cookie'] = implode('; ', $cookieString);
        }

        $output = $this->method . ' ' . $this->uri . ' ' . $this->protocol . "\r\n";
        $output .= (string) $this->header;
        $output .= $body;

        return $output;
    }

    /**
     * 获取或者设置GET数据
     *
     * @param   mixed  $key   Key or key value pairs to set
     * @param   string $value Value to set to a key
     *
     * @return  mixed
     * @uses    Arr::path
     */
    public function query($key = null, $value = null)
    {
        if (is_array($key))
        {
            // Act as a setter, replace all query strings
            $this->_get = $key;

            return $this;
        }

        if (null === $key)
        {
            // Act as a getter, all query strings
            return $this->_get;
        }
        elseif (null === $value)
        {
            // Act as a getter, single query string
            return Arr::path($this->_get, $key);
        }

        // Act as a setter, single query string
        $this->_get[$key] = $value;

        return $this;
    }

    /**
     * 读取或者设置post数据
     *
     * @param   mixed  $key   Key or key value pairs to set
     * @param   string $value Value to set to a key
     *
     * @return  mixed
     */
    public function post($key = null, $value = null)
    {
        if (is_array($key))
        {
            // Act as a setter, replace all fields
            $this->_post = $key;
            return $this;
        }
        if (null === $key)
        {
            // Act as a getter, all fields
            return $this->_post;
        }
        elseif (null === $value)
        {
            // Act as a getter, single field
            return Arr::path($this->_post, $key);
        }
        // Act as a setter, single field
        $this->_post[$key] = $value;

        return $this;
    }
}

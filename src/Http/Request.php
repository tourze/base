<?php

namespace tourze\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use tourze\Base\Object;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Url;
use tourze\Base\Base;
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
 * @property   RequestClient client
 * @property   Route         route
 * @property   Message       message
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
class Request extends Object implements RequestInterface
{

    /**
     * @var string 客户端UA
     */
    public static $userAgent = '';

    /**
     * @var string 客户端IP
     */
    public static $clientIp = '0.0.0.0';

    /**
     * @var string 可信任的代理服务器列表
     */
    public static $trustedProxies = [
        '127.0.0.1',
        'localhost',
    ];

    /**
     * @var static 系统主请求
     */
    public static $initial;

    /**
     * @var static 当前正在处理的请求
     */
    public static $current;

    /**
     * 创建一个新的实例
     *
     *     $request = Request::factory($uri);
     *
     * If $cache parameter is set, the response for the request will attempt to be retrieved from the cache.
     *
     * @param   bool|string $uri            URI
     * @param   array       $clientParams   注入到client中的参数
     * @param   array       $injectedRoutes 注入到路由中的参数，一般用于测试
     * @return  Request|void
     * @throws  BaseException
     */
    public static function factory($uri = true, $clientParams = [], $injectedRoutes = [])
    {
        $request = new Request($uri, $clientParams, $injectedRoutes);

        return $request;
    }

    /**
     * 当前当前正在处理的请求
     *
     * @return Request
     */
    public static function current()
    {
        return Request::$current;
    }

    /**
     * 返回系统的初始请求
     *
     *     $request = Request::initial();
     *     // 检测当前请求是否就是初始请求
     *     if (Request::initial() === Request::current()) { }
     *
     * @return Request
     */
    public static function initial()
    {
        return Request::$initial;
    }

    /**
     * 解析请求，查找路由
     *
     * @param  Request $request Request
     * @param  Route[] $routes  Route
     * @return array
     */
    public static function process(Request $request, $routes = null)
    {
        $routes = (empty($routes)) ? Route::all() : $routes;
        $params = null;

        foreach ($routes as $name => $route)
        {
            /* @var $route Route */
            if ($params = $route->matches($request->uri, $request->method))
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
     * 读取客户端IP地址
     *
     * @return string
     */
    public static function getClientIP()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && isset($_SERVER['REMOTE_ADDR'])
            && in_array($_SERVER['REMOTE_ADDR'], Request::$trustedProxies)
        )
        {
            // Format: "X-Forwarded-For: client1, proxy1, proxy2"
            $clientIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return array_shift($clientIps);
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])
            && isset($_SERVER['REMOTE_ADDR'])
            && in_array($_SERVER['REMOTE_ADDR'], Request::$trustedProxies)
        )
        {
            $clientIps = explode(',', $_SERVER['HTTP_CLIENT_IP']);

            return array_shift($clientIps);
        }
        elseif (isset($_SERVER['REMOTE_ADDR']))
        {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '0.0.0.0';
    }

    /**
     * Parses an accept header and returns an array (type => quality) of the accepted types, ordered by quality.
     *
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

    /**
     * @param null|Message $message
     * @return Request
     */
    public function setMessage($message)
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * @return null|Message
     */
    public function getMessage()
    {
        return $this->_message;
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
     * @var string 请求方法，GET、POST或其他
     */
    protected $_method = 'GET';

    /**
     * 读取当前请求方法
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * 设置当前请求方法
     *
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->_method = strtoupper($method);
        return $this;
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
     * @var string referring URL
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
     * 读取当前的body内容
     *
     * @return string
     */
    public function getBody()
    {
        return $this->message->body;
    }

    /**
     * 设置body内容
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->message->body = $body;
        return $this;
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

    /**
     * 读取当前URI
     *
     * @return string
     */
    public function getUri()
    {
        return empty($this->_uri) ? '/' : $this->_uri;
    }

    /**
     * 设置URI
     *
     * @param mixed $uri
     */
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
     * @var null|Message
     */
    protected $_message = null;

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

    /**
     * @var bool|static
     */
    protected $_initial = false;

    /**
     * 根据URI创建一个新的请求对象
     *
     *     $request = new Request($uri);
     *
     * If $cache parameter is set, the response for the request will attempt to be retrieved from the cache.
     *
     * @param  string $uri            URI of the request
     * @param  array  $clientParams   Array of params to pass to the request client
     * @param  array  $injectedRoutes An array of routes to use, for testing
     * @throws RequestException
     */
    public function __construct($uri, $clientParams = [], $injectedRoutes = [])
    {
        $clientParams = is_array($clientParams) ? $clientParams : [];

        $this->message = new Message;
        $this->routes = $injectedRoutes;

        $splitUri = explode('?', $uri);
        $uri = array_shift($splitUri);

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
            $this->uri = $uri;

            if (0 === strpos($uri, 'https://'))
            {
                $this->secure = true;
            }

            $this->external = true;
            $this->client = new ExternalClient($clientParams);
        }
        else
        {
            $this->uri = trim($uri, '/');
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
            $this->message->setHeaders(Http::requestHeaders());
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
                    // 控制器放在子目录中的情况
                    $this->directory = $params['directory'];
                }

                // 附加上命名空间
                if (isset($params['namespace']))
                {
                    $this->directory = $params['namespace'];
                }

                // 命名空间处理
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
            $e = HttpException::factory(Message::NOT_FOUND, 'Unable to find a route to match the URI: :uri', [
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
     * 读取或者设置header信息
     *
     * @param  string|array $name  头部名或包含了头部名和数据的数组
     * @param  string       $value 值
     * @return mixed|$this
     */
    public function headers($name = null, $value = null)
    {
        if (is_array($name))
        {
            foreach ($name as $k => $v)
            {
                $this->message->withHeader($k, $v);
            }
            return $this;
        }

        if (null === $name)
        {
            return $this->message->getHeaders();
        }
        elseif (null === $value)
        {
            return $this->message->getHeaderLine($name);
        }

        $this->message->withHeader($name, $value);

        return $this;
    }

    /**
     * Set and get cookies values for this request.
     *
     * @param   mixed  $key   CookieHelper name, or array of cookie values
     * @param   string $value Value to set to cookie
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
    public function contentLength()
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
        $this->headers('content-length', (string) $this->contentLength());

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

            // 创建cookie字符串
            $this->headers('cookie', implode('; ', $cookieString));
        }

        $output = $this->method . ' ' . $this->uri . ' ' . $this->protocol . "\r\n";
        $output .= $this->message->headerLines;
        $output .= $body;

        return $output;
    }

    /**
     * 获取或者设置GET数据
     *
     * @param  mixed  $key   Key or key value pairs to set
     * @param  string $value Value to set to a key
     * @return mixed
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
     * @param  mixed  $key   Key or key value pairs to set
     * @param  string $value Value to set to a key
     * @return mixed
     */
    public function post($key = null, $value = null)
    {
        if (is_array($key))
        {
            $this->_post = $key;
            return $this;
        }
        if (null === $key)
        {
            return $this->_post;
        }
        elseif (null === $value)
        {
            return Arr::path($this->_post, $key);
        }
        $this->_post[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->message->getProtocolVersion();
    }

    /**
     * @param string $version
     * @return $this
     */
    public function withProtocolVersion($version)
    {
        $this->message->withProtocolVersion($version);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->message->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->message->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return $this->message->getHeaderLine($name);
    }

    /**
     * @param string           $name
     * @param string|\string[] $value
     * @return $this
     */
    public function withHeader($name, $value)
    {
        $this->message->withHeader($name, $value);
        return $this;
    }

    /**
     * @param string           $name
     * @param string|\string[] $value
     * @return $this
     */
    public function withAddedHeader($name, $value)
    {
        $this->message->withAddedHeader($name, $value);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function withoutHeader($name)
    {
        $this->message->withoutHeader($name);
        return $this;
    }

    /**
     * @param \Psr\Http\Message\StreamInterface $body
     * @return $this
     */
    public function withBody(StreamInterface $body)
    {
        $this->message->withBody($body);
        return $this;
    }

    /**
     * @param string $name
     * @return $this|mixed|\tourze\Http\Request
     */
    public function getHeader($name)
    {
        return $this->headers($name);
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        return $this->uri;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        $this->uri = $requestTarget;
        return $this;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = (string) $uri;
        return $this;
    }
}

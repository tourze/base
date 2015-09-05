<?php

namespace tourze\Base\Component;

use tourze\Base\Base;
use tourze\Base\Component;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Url;

/**
 * 基础的HTTP组件
 *
 * @package tourze\Base\Component
 */
class Http extends Component
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
    const MERGE   = 'MERGE';
    const PATCH   = 'PATCH';
    const COPY    = 'COPY';

    /**
     * @var int
     */
    const CONTINUES = 100;

    /**
     * @var int
     */
    const SWITCHING_PROTOCOLS = 101;

    /**
     * @var int
     */
    const OK = 200;

    /**
     * @var int
     */
    const CREATED = 201;

    /**
     * @var int
     */
    const ACCEPTED = 202;

    /**
     * @var int
     */
    const NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * @var int
     */
    const NO_CONTENT = 204;

    /**
     * @var int
     */
    const RESET_CONTENT = 205;

    /**
     * @var int
     */
    const PARTIAL_CONTENT = 206;

    /**
     * @var int
     */
    const MULTIPLE_CHOICES = 300;

    /**
     * @var int
     */
    const MOVED_PERMANENTLY = 301;

    /**
     * @var int
     */
    const FOUND = 302;

    /**
     * @var int
     */
    const SEE_OTHER = 303;

    /**
     * @var int
     */
    const NOT_MODIFIED = 304;

    /**
     * @var int
     */
    const USE_PROXY = 305;

    /**
     * @var int
     */
    const TEMPORARY_REDIRECT = 307;

    /**
     * @var int
     */
    const BAD_REQUEST = 400;

    /**
     * @var int
     */
    const UNAUTHORIZED = 401;

    /**
     * @var int
     */
    const PAYMENT_REQUIRED = 402;

    /**
     * @var int
     */
    const FORBIDDEN = 403;

    /**
     * @var int
     */
    const NOT_FOUND = 404;

    /**
     * @var int
     */
    const METHOD_NOT_ALLOWED = 405;

    /**
     * @var int
     */
    const NOT_ACCEPTABLE = 406;

    /**
     * @var int
     */
    const PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * @var int
     */
    const REQUEST_TIMEOUT = 408;

    /**
     * @var int
     */
    const CONFLICT = 409;

    /**
     * @var int
     */
    const GONE = 410;

    /**
     * @var int
     */
    const LENGTH_REQUIRED = 411;

    /**
     * @var int
     */
    const PRECONDITION_FAILED = 412;

    /**
     * @var int
     */
    const REQUEST_ENTITY_TOO_LARGE = 413;

    /**
     * @var int
     */
    const REQUEST_URI_TOO_LONG = 414;

    /**
     * @var int
     */
    const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @var int
     */
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    /**
     * @var int
     */
    const EXPECTATION_FAILED = 417;

    /**
     * @var int
     */
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * @var int
     */
    const NOT_IMPLEMENTED = 501;

    /**
     * @var int
     */
    const BAD_GATEWAY = 502;

    /**
     * @var int
     */
    const SERVICE_UNAVAILABLE = 503;

    /**
     * @var int
     */
    const GATEWAY_TIMEOUT = 504;

    /**
     * @var int
     */
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @var int
     */
    const BANDWIDTH_LIMIT_EXCEEDED = 509;

    /**
     * @var array 状态码和对应消息
     */
    public static $text = [
        // Informational 1xx
        self::CONTINUES                       => 'Continue',
        self::SWITCHING_PROTOCOLS             => 'Switching Protocols',

        // Success 2xx
        self::OK                              => 'OK',
        self::CREATED                         => 'Created',
        self::ACCEPTED                        => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION   => 'Non-Authoritative Information',
        self::NO_CONTENT                      => 'No Content',
        self::RESET_CONTENT                   => 'Reset Content',
        self::PARTIAL_CONTENT                 => 'Partial Content',

        // Redirection 3xx
        self::MULTIPLE_CHOICES                => 'Multiple Choices',
        self::MOVED_PERMANENTLY               => 'Moved Permanently',
        self::FOUND                           => 'Found',
        // 1.1
        self::SEE_OTHER                       => 'See Other',
        self::NOT_MODIFIED                    => 'Not Modified',
        self::USE_PROXY                       => 'Use Proxy',
        // 306 is deprecated but reserved
        self::TEMPORARY_REDIRECT              => 'Temporary Redirect',

        // Client Error 4xx
        self::BAD_REQUEST                     => 'Bad Request',
        self::UNAUTHORIZED                    => 'Unauthorized',
        self::PAYMENT_REQUIRED                => 'Payment Required',
        self::FORBIDDEN                       => 'Forbidden',
        self::NOT_FOUND                       => 'Not Found',
        self::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
        self::NOT_ACCEPTABLE                  => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT                 => 'Request Timeout',
        self::CONFLICT                        => 'Conflict',
        self::GONE                            => 'Gone',
        self::LENGTH_REQUIRED                 => 'Length Required',
        self::PRECONDITION_FAILED             => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE        => 'Request Entity Too Large',
        self::REQUEST_URI_TOO_LONG            => 'Request-URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED              => 'Expectation Failed',

        // Server Error 5xx
        self::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
        self::NOT_IMPLEMENTED                 => 'Not Implemented',
        self::BAD_GATEWAY                     => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE             => 'Service Unavailable',
        self::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
        self::BANDWIDTH_LIMIT_EXCEEDED        => 'Bandwidth Limit Exceeded'
    ];

    /**
     * @var string 默认HTTP协议
     */
    public $protocol = 'HTTP/1.1';

    /**
     * 解析请求，并读取其中的HEADER信息
     *
     * @return array
     */
    public static function requestHeaders()
    {
        Base::getLog()->debug(__METHOD__ . ' parse requested headers');

        // apache服务器
        if (function_exists('apache_request_headers'))
        {
            return apache_request_headers();
        }

        // PECL扩展加载了
        elseif (extension_loaded('http'))
        {
            return http_get_request_headers();
        }

        $headers = [];

        if ( ! empty($_SERVER['CONTENT_TYPE']))
        {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        if ( ! empty($_SERVER['CONTENT_LENGTH']))
        {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }

        foreach ($_SERVER as $key => $value)
        {
            // 跳过非HTTP开头的值
            if (strpos($key, 'HTTP_') !== 0)
            {
                continue;
            }

            $key = str_replace(['HTTP_', '_'], ['', '-'], $key);
            $key = strtolower($key);
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * 退出当前http请求
     *
     * @param string $msg
     */
    public function end($msg = '')
    {
        Base::getLog()->notice(__METHOD__ . ' exit process');

        echo $msg;
        exit;
    }

    /**
     * 输出指定code
     *
     * @param int $code
     */
    public function code($code = null)
    {
        Base::getLog()->debug(__METHOD__ . ' response status code', [
            'code' => $code,
        ]);
        $text = Arr::get(self::$text, $code, false);
        if ( ! $text)
        {
            return;
        }

        $this->header($this->protocol . ' ' . $code . ' ' . $text);
    }

    /**
     * 跳转
     *
     * @param  string $uri  要跳转的URI
     * @param  int    $code 跳转时使用的http状态码
     */
    public function redirect($uri = '', $code = 302)
    {
        Base::getLog()->debug(__METHOD__ . ' redirect page', [
            'url'  => $uri,
            'code' => $code,
        ]);

        if (false === strpos($uri, '://'))
        {
            $uri = Url::site($uri, true, ! empty(Base::$indexFile));
        }

        $lastTime = gmdate("D, d M Y H:i:s", time()) . ' GMT+0800';
        $this->header('Cache-Control: no-cache');
        $this->header('Last Modified: ' . $lastTime);
        $this->header('Last Fetched: ' . $lastTime);
        $this->header('Expires: Thu Jan 01 1970 08:00:00 GMT+0800');
        $this->header('Location: ' . $uri);

        $this->code($code);

        $this->end();
    }

    /**
     * 写cookie
     *
     * @param string $name
     * @param string $value
     * @param int    $maxAge
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return bool
     */
    public function setCookie($name, $value = '', $maxAge = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
    {
        Base::getLog()->debug(__METHOD__ . ' set cookie', [
            'name'      => $name,
            'value'     => $value,
            'expire'    => $maxAge,
            'path'      => $path,
            'domain'    => $domain,
            'secure'    => $secure,
            'http_only' => $httpOnly
        ]);
        return setcookie($name, $value, $maxAge, $path, $domain, $secure, $httpOnly);
    }

    /**
     * 开始会话
     *
     * @return bool
     */
    public function sessionStart()
    {
        Base::getLog()->debug(__METHOD__ . ' call to session start');
        if (session_status() == PHP_SESSION_NONE)
        {
            return session_start();
        }
        return false;
    }

    /**
     * 返回会话ID
     *
     * @param mixed $id
     * @return string
     */
    public function sessionID($id = null)
    {
        Base::getLog()->debug(__METHOD__ . ' get session id', [
            'id' => $id,
        ]);
        return session_id($id);
    }

    /**
     * 重新返回一个会话ID
     *
     * @param bool|false $deleteOldSession
     * @return bool
     */
    public function sessionRegenerateID($deleteOldSession = false)
    {
        Base::getLog()->debug(__METHOD__ . ' regenerate session id');
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * 结束会话
     */
    public function sessionWriteClose()
    {
        Base::getLog()->debug(__METHOD__ . ' call session write close');
        session_write_close();
    }

    /**
     * 输出头部信息
     *
     * @param string    $string
     * @param bool|true $replace
     * @param null|int  $httpResponseCode
     */
    public function header($string, $replace = true, $httpResponseCode = null)
    {
        Base::getLog()->debug(__METHOD__ . ' response header', [
            'header'  => $string,
            'replace' => $replace,
            'code'    => $httpResponseCode
        ]);
        header($string, $replace, $httpResponseCode);
    }

    /**
     * @param string $name
     */
    public function headerRemove($name = null)
    {
        Base::getLog()->debug(__METHOD__ . ' remove header', [
            'name'  => $name,
        ]);
        header_remove($name);
    }

    /**
     * @return array
     */
    public function headersList()
    {
        Base::getLog()->debug(__METHOD__ . ' fetch header list');
        return headers_list();
    }

    /**
     * @param string $file
     * @param string $line
     * @return bool
     */
    public function headersSent(&$file = null, &$line = null)
    {
        Base::getLog()->debug(__METHOD__ . ' check if header sent', [
            'file' => $file,
            'line' => $line,
        ]);
        return headers_sent($file, $line);
    }
}

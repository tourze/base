<?php

namespace tourze\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use tourze\Base\Helper\Arr;
use tourze\Base\Object;

/**
 * HTTP消息
 *
 * @package tourze\Http
 * @property string                 protocolVersion
 * @property array                  headers
 * @property string                 headerLines
 * @property string|StreamInterface body
 */
class Message extends Object implements MessageInterface
{

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
        Message::CONTINUES                       => 'Continue',
        Message::SWITCHING_PROTOCOLS             => 'Switching Protocols',

        // Success 2xx
        Message::OK                              => 'OK',
        Message::CREATED                         => 'Created',
        Message::ACCEPTED                        => 'Accepted',
        Message::NON_AUTHORITATIVE_INFORMATION   => 'Non-Authoritative Information',
        Message::NO_CONTENT                      => 'No Content',
        Message::RESET_CONTENT                   => 'Reset Content',
        Message::PARTIAL_CONTENT                 => 'Partial Content',

        // Redirection 3xx
        Message::MULTIPLE_CHOICES                => 'Multiple Choices',
        Message::MOVED_PERMANENTLY               => 'Moved Permanently',
        Message::FOUND                           => 'Found',
        // 1.1
        Message::SEE_OTHER                       => 'See Other',
        Message::NOT_MODIFIED                    => 'Not Modified',
        Message::USE_PROXY                       => 'Use Proxy',
        // 306 is deprecated but reserved
        Message::TEMPORARY_REDIRECT              => 'Temporary Redirect',

        // Client Error 4xx
        Message::BAD_REQUEST                     => 'Bad Request',
        Message::UNAUTHORIZED                    => 'Unauthorized',
        Message::PAYMENT_REQUIRED                => 'Payment Required',
        Message::FORBIDDEN                       => 'Forbidden',
        Message::NOT_FOUND                       => 'Not Found',
        Message::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
        Message::NOT_ACCEPTABLE                  => 'Not Acceptable',
        Message::PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
        Message::REQUEST_TIMEOUT                 => 'Request Timeout',
        Message::CONFLICT                        => 'Conflict',
        Message::GONE                            => 'Gone',
        Message::LENGTH_REQUIRED                 => 'Length Required',
        Message::PRECONDITION_FAILED             => 'Precondition Failed',
        Message::REQUEST_ENTITY_TOO_LARGE        => 'Request Entity Too Large',
        Message::REQUEST_URI_TOO_LONG            => 'Request-URI Too Long',
        Message::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
        Message::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        Message::EXPECTATION_FAILED              => 'Expectation Failed',

        // Server Error 5xx
        Message::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
        Message::NOT_IMPLEMENTED                 => 'Not Implemented',
        Message::BAD_GATEWAY                     => 'Bad Gateway',
        Message::SERVICE_UNAVAILABLE             => 'Service Unavailable',
        Message::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
        Message::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
        Message::BANDWIDTH_LIMIT_EXCEEDED        => 'Bandwidth Limit Exceeded'
    ];

    /**
     * @var string 当前协议版本
     */
    protected $_protocolVersion = '1.1';

    /**
     * @var array HEADER信息数组
     */
    protected $_headers = [];

    /**
     * @var string|StreamInterface
     */
    protected $_body = '';

    /**
     * 返回当前协议版本，如1.1或1.0
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->_protocolVersion;
    }

    /**
     * 设置协议版本
     *
     * @param string $protocolVersion
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->_protocolVersion = $protocolVersion;
    }

    /**
     * 返回一个指定协议版本的消息实例
     *
     * @param string $version HTTP版本
     * @return self
     */
    public function withProtocolVersion($version)
    {
        return new self([
            'protocolVersion' => $version
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * 一次性设置多个header
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return isset($this->_headers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return isset($this->_headers[$name]) ? $this->_headers[$name] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        if ( ! isset($this->_headers[$name]))
        {
            return '';
        }

        return implode(', ', $this->_headers[$name]);
    }

    /**
     * 返回一个完整的header
     *
     * @return string
     */
    public function getHeaderLines()
    {
        $header = '';

        foreach ($this->getHeaders() as $key => $value)
        {
            // 格式化header的key
            $key = explode('-', $key);
            foreach ($key as $k => $v)
            {
                $key[$k] = ucfirst($v);
            }
            $key = implode('-', $key);

            if (is_array($value))
            {
                $header .= $key . ': ' . (implode(', ', $value));
            }
            else
            {
                $header .= $key . ': ' . $value;
            }
            $header .= "\r\n";
        }

        return $header . "\r\n";
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        if ( ! is_array($value))
        {
            $value = [$value];
        }

        $this->_headers[$name] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        if (isset($this->_headers[$name]))
        {
            // 如果是数组，那么合并
            if (is_array($value))
            {
                $this->_headers[$name] = Arr::merge($this->_headers[$name], $value);
            }
            // 否则直接新增
            else
            {
                $this->_headers[$name][] = $value;
            }
        }
        else
        {
            $this->withHeader($name, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        if (isset($this->_headers[$name]))
        {
            unset($this->_headers[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * 保存body
     *
     * @param StreamInterface|string $body
     * @return Message
     */
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        return new self([
            'body' => $body
        ]);
    }
}

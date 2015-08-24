<?php

namespace tourze\Http\Exception;

use Exception;
use tourze\Base\Exception\BaseException;
use tourze\Http\Request;
use tourze\Http\Response;

/**
 * 基础的HTTP异常类
 *
 * @package tourze\Http\Exception
 */
abstract class HttpException extends BaseException
{

    /**
     * Creates an HTTP_Exception of the specified type.
     *
     * @param   int   $code      the http status code
     * @param   string    $message   status message, custom content to display with error
     * @param   array     $variables translation variables
     * @param   Exception $previous
     *
     * @return  HttpException
     */
    public static function factory($code, $message = null, array $variables = null, Exception $previous = null)
    {
        $class = 'tourze\Http\Exception\Http'.$code.'Exception';

        return new $class($message, $variables, $previous);
    }

    /**
     * @var string
     */
    protected $_uri = '';

    /**
     * @var int HTTP状态码
     */
    protected $_code = 0;

    /**
     * @var  Request  Request instance that triggered this exception.
     */
    protected $_request;

    /**
     * @var Response Response对象
     */
    protected $_response;

    /**
     * Creates a new translated exception.
     *
     * @param   string    $message   status message, custom content to display with error
     * @param   array     $variables translation variables
     * @param   Exception $previous
     */
    public function __construct($message = null, array $variables = null, Exception $previous = null)
    {
        parent::__construct($message, $variables, $this->_code, $previous);

        // 准备一个response对象
        $this->_response = Response::factory();
        $this->_response->status = $this->_code;
    }

    /**
     * Store the Request that triggered this exception.
     *
     * @param   Request $request Request object that triggered this exception.
     *
     * @return  HttpException
     */
    public function request(Request $request = null)
    {
        if (null === $request)
        {
            return $this->_request;
        }
        $this->_request = $request;
        return $this;
    }

    /**
     * Generate a Response for the current Exception
     */
    public function getResponse()
    {
        BaseException::response($this);
    }

    /**
     * 异常抛出时跳转的地址
     *
     * @param string $uri
     * @return $this
     */
    public function location($uri = '')
    {
        $this->_uri = $uri;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_code;
    }

    /**
     * @param int $code
     */
    public function setStatusCode($code)
    {
        $this->_code = $code;
    }
}

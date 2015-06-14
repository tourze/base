<?php

namespace tourze\Http\Exception;

use Exception;
use tourze\Base\Exception\BaseException;
use tourze\Http\HttpRequest;

abstract class HttpException extends BaseException
{

    /**
     * Creates an HTTP_Exception of the specified type.
     *
     * @param   integer   $code      the http status code
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
     * @var  int        http status code
     */
    protected $_code = 0;

    /**
     * @var  HttpRequest    Request instance that triggered this exception.
     */
    protected $_request;

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
    }

    /**
     * Store the Request that triggered this exception.
     *
     * @param   HttpRequest $request Request object that triggered this exception.
     *
     * @return  HttpException
     */
    public function request(HttpRequest $request = null)
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
        return BaseException::response($this);
    }

    public function location($uri = null)
    {
        return $this;
    }
}

<?php

namespace tourze\Http\Exception;

use Exception;
use tourze\Base\Exception\BaseException;
use tourze\Http\HttpResponse;

/**
 * Http异常，一般用于那些不需要显示错误信息的报错，如301、302
 *
 * @package    Base
 * @category   Exceptions
 * @author     YwiSax
 */
abstract class ExpectedException extends HttpException
{

    /**
     * @var  HttpResponse   Response Object
     */
    protected $_response;

    /**
     * Creates a new translated exception.
     *     throw new BaseException('Something went terrible wrong, :user',
     *         [':user' => $user]);
     *
     * @param   string    $message   status message, custom content to display with error
     * @param   array     $variables translation variables
     * @param   Exception $previous
     * @throws  BaseException
     */
    public function __construct($message = null, array $variables = null, Exception $previous = null)
    {
        parent::__construct($message, $variables, $previous);

        // Prepare our response object and set the correct status code.
        $this->_response = HttpResponse::factory();
        $this->_response->status = $this->_code;
    }

    /**
     * Gets and sets headers to the [Response].
     *
     * @see     [Response::headers]
     * @param   mixed  $key
     * @param   string $value
     * @return  mixed
     */
    public function headers($key = null, $value = null)
    {
        if (null === $value)
        {
            return $this->_response->headers($key);
        }

        $result = $this->_response->headers($key, $value);

        if ( ! $result instanceof HttpResponse)
        {
            return $result;
        }

        return $this;
    }

    /**
     * Validate this exception contains everything needed to continue.
     *
     * @throws BaseException
     * @return bool
     */
    public function check()
    {
        return true;
    }

    /**
     * Generate a Response for the current Exception
     *
     * @uses   BaseException::response()
     * @return HttpResponse
     */
    public function getResponse()
    {
        $this->check();

        return $this->_response;
    }

}

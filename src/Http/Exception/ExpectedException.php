<?php

namespace tourze\Http\Exception;

use tourze\Base\Exception\BaseException;
use tourze\Http\Response;

/**
 * Http异常，一般用于那些不需要显示错误信息的报错，如301、302
 *
 * @package tourze\Http\Exception
 */
abstract class ExpectedException extends HttpException
{

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

        if ( ! $result instanceof Response)
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
     * @return Response
     */
    public function getResponse()
    {
        $this->check();

        return $this->_response;
    }

}

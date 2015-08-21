<?php

namespace tourze\Http\Exception;

use tourze\Base\Exception\BaseException;

class Http405Exception extends ExpectedException
{

    /**
     * @var   int    HTTP 405 Method Not Allowed
     */
    protected $_code = 405;

    /**
     * Specifies the list of allowed HTTP methods
     *
     * @param  array $methods List of allowed methods
     * @return $this
     */
    public function allowed($methods)
    {
        if (is_array($methods))
        {
            $methods = implode(',', $methods);
        }

        $this->headers('allow', $methods);

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
        if (null === ($location = $this->headers('allow')))
        {
            throw new BaseException('A list of allowed methods must be specified');
        }

        return true;
    }

}

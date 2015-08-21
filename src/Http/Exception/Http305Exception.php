<?php

namespace tourze\Http\Exception;

use tourze\Base\Exception\BaseException;

class Http305Exception extends ExpectedException
{

    /**
     * @var   int    HTTP 305 Use Proxy
     */
    protected $_code = 305;

    /**
     * Specifies the proxy to replay this request via
     *
     * @param null $uri
     * @return $this
     */
    public function location($uri = null)
    {
        if (null === $uri)
        {
            return $this->headers('Location');
        }
        $this->headers('Location', $uri);

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
        if (null === ($location = $this->headers('location')))
        {
            throw new BaseException("A 'location' must be specified for a redirect");
        }

        if (false === strpos($location, '://'))
        {
            throw new BaseException('An absolute URI to the proxy server must be specified');
        }

        return true;
    }

}

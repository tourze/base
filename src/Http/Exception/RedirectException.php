<?php

namespace tourze\Http\Exception;

use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Url;
use tourze\Base\Base;

/**
 * Redirect HTTP exception class. Used for all [HTTP_Exception]'s where the status
 * code indicates a redirect.
 * Eg [HTTP_Exception_301], [HTTP_Exception_302] and most of the other 30x's
 *
 * @package    Base
 * @category   Exceptions
 * @author     YwiSax
 */
abstract class RedirectException extends ExpectedException
{

    /**
     * Specifies the URI to redirect to.
     *
     * @param   string $uri
     * @return  $this
     */
    public function location($uri = null)
    {
        if (null === $uri)
        {
            return $this->headers('Location');
        }

        if (false === strpos($uri, '://'))
        {
            // Make the URI into a URL
            $uri = Url::site($uri, true, ! empty(Base::$indexFile));
        }

        $lastTime = gmdate("D, d M Y H:i:s", time()).' GMT+0800';
        $this->headers('Cache-Control', 'no-cache');
        $this->headers('Last Modified', $lastTime);
        $this->headers('Last Fetched', $lastTime);
        $this->headers('Expires', 'Thu Jan 01 1970 08:00:00 GMT+0800');
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
        if (null === $this->headers('location'))
        {
            throw new BaseException("A 'location' must be specified for a redirect");
        }

        return true;
    }

}

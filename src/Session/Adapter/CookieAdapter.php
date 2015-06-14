<?php

namespace tourze\Session\Adapter;

use tourze\Base\Helper\Cookie;
use tourze\Session\SessionAdapter;

/**
 * CookieHelper-based session class.
 *
 * @package    Base
 * @category   Session
 * @author     YwiSax
 */
class CookieAdapter extends SessionAdapter
{

    /**
     * @param   string $id session id
     * @return  string
     */
    protected function _read($id = null)
    {
        return Cookie::get($this->_name, null);
    }

    /**
     * @return  null
     */
    protected function _regenerate()
    {
        // CookieHelper sessions have no id
        return null;
    }

    /**
     * @return  bool
     */
    protected function _write()
    {
        return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
    }

    /**
     * @return  bool
     */
    protected function _restart()
    {
        return true;
    }

    /**
     * @return  bool
     */
    protected function _destroy()
    {
        return Cookie::delete($this->_name);
    }

}

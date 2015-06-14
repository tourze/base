<?php

namespace tourze\Http\Exception;

class Http302Exception extends RedirectException
{

    /**
     * @var   integer    HTTP 302 Found
     */
    protected $_code = 302;

}

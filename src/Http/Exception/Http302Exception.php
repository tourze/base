<?php

namespace tourze\Http\Exception;

class Http302Exception extends RedirectException
{

    /**
     * @var   int    HTTP 302 Found
     */
    protected $_code = 302;

}

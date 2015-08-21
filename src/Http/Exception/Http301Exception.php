<?php

namespace tourze\Http\Exception;

class Http301Exception extends RedirectException
{

    /**
     * @var   int    HTTP 301 Moved Permanently
     */
    protected $_code = 301;

}

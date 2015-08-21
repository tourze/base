<?php

namespace tourze\Http\Exception;

class Http303Exception extends RedirectException
{

    /**
     * @var   int    HTTP 303 See Other
     */
    protected $_code = 303;

}

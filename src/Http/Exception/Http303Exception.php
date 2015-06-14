<?php

namespace tourze\Http\Exception;

class Http303Exception extends RedirectException
{

    /**
     * @var   integer    HTTP 303 See Other
     */
    protected $_code = 303;

}

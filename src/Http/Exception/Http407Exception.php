<?php

namespace tourze\Http\Exception;

class Http407Exception extends HttpException
{

    /**
     * @var   int    HTTP 407 Proxy Authentication Required
     */
    protected $_code = 407;

}

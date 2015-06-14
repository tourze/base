<?php

namespace tourze\Http\Exception;

class Http407Exception extends HttpException
{

    /**
     * @var   integer    HTTP 407 Proxy Authentication Required
     */
    protected $_code = 407;

}

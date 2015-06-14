<?php

namespace tourze\Http\Exception;

class Http500Exception extends HttpException
{

    /**
     * @var   integer    HTTP 500 Internal Server Error
     */
    protected $_code = 500;

}

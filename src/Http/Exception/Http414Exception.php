<?php

namespace tourze\Http\Exception;

class Http414Exception extends HttpException
{

    /**
     * @var   int    HTTP 414 Request-URI Too Long
     */
    protected $_code = 414;

}

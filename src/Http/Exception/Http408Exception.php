<?php

namespace tourze\Http\Exception;

class Http408Exception extends HttpException
{

    /**
     * @var   int    HTTP 408 Request Timeout
     */
    protected $_code = 408;

}

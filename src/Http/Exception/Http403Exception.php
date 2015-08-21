<?php

namespace tourze\Http\Exception;

class Http403Exception extends HttpException
{

    /**
     * @var   int    HTTP 403 Forbidden
     */
    protected $_code = 403;

}

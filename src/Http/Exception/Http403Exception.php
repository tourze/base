<?php

namespace tourze\Http\Exception;

class Http403Exception extends HttpException
{

    /**
     * @var   integer    HTTP 403 Forbidden
     */
    protected $_code = 403;

}

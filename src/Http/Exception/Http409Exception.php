<?php

namespace tourze\Http\Exception;

class Http409Exception extends HttpException
{

    /**
     * @var   integer    HTTP 409 Conflict
     */
    protected $_code = 409;

}

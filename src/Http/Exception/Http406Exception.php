<?php

namespace tourze\Http\Exception;

class Http406Exception extends HttpException
{

    /**
     * @var   integer    HTTP 406 Not Acceptable
     */
    protected $_code = 406;

}

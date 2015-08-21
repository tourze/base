<?php

namespace tourze\Http\Exception;

class Http406Exception extends HttpException
{

    /**
     * @var   int    HTTP 406 Not Acceptable
     */
    protected $_code = 406;

}

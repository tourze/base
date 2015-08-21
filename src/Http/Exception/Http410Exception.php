<?php

namespace tourze\Http\Exception;

class Http410Exception extends HttpException
{

    /**
     * @var   int    HTTP 410 Gone
     */
    protected $_code = 410;

}

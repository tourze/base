<?php

namespace tourze\Http\Exception;

class Http501Exception extends HttpException
{

    /**
     * @var   int    HTTP 501 Not Implemented
     */
    protected $_code = 501;

}

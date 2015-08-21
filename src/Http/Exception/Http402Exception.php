<?php

namespace tourze\Http\Exception;

class Http402Exception extends HttpException
{

    /**
     * @var   int    HTTP 402 Payment Required
     */
    protected $_code = 402;

}

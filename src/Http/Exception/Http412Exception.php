<?php

namespace tourze\Http\Exception;

class Http412Exception extends HttpException
{

    /**
     * @var   int    HTTP 412 Precondition Failed
     */
    protected $_code = 412;

}

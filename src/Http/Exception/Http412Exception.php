<?php

namespace tourze\Http\Exception;

class Http412Exception extends HttpException
{

    /**
     * @var   integer    HTTP 412 Precondition Failed
     */
    protected $_code = 412;

}

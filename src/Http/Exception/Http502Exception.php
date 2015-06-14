<?php

namespace tourze\Http\Exception;

class Http502Exception extends HttpException
{

    /**
     * @var   integer    HTTP 502 Bad Gateway
     */
    protected $_code = 502;

}

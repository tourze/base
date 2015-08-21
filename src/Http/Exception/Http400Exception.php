<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http400Exception extends HttpException
{

    /**
     * @var   int    HTTP 400 Bad Request
     */
    protected $_code = Message::BAD_REQUEST;

}

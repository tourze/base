<?php

namespace tourze\Http\Exception;

class Http410Exception extends HttpException
{

    /**
     * @var   integer    HTTP 410 Gone
     */
    protected $_code = 410;

}

<?php

namespace tourze\Http\Exception;

class Http417Exception extends HttpException
{

    /**
     * @var   integer    HTTP 417 Expectation Failed
     */
    protected $_code = 417;

}

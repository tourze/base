<?php

namespace tourze\Http\Exception;

class Http411Exception extends HttpException
{

    /**
     * @var   integer    HTTP 411 Length Required
     */
    protected $_code = 411;

}

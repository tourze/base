<?php

namespace tourze\Http\Exception;

class Http505Exception extends HttpException
{

    /**
     * @var   integer    HTTP 505 HTTP Version Not Supported
     */
    protected $_code = 505;

}

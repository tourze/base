<?php

namespace tourze\Http\Exception;

class Http404Exception extends HttpException
{

    /**
     * @var   int    HTTP 404 Not Found
     */
    protected $_code = 404;

}

<?php

namespace tourze\Http\Exception;

class Http416Exception extends HttpException
{

    /**
     * @var   integer    HTTP 416 Request Range Not Satisfiable
     */
    protected $_code = 416;

}

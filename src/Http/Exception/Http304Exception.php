<?php

namespace tourze\Http\Exception;

class Http304Exception extends ExpectedException
{

    /**
     * @var   int    HTTP 304 Not Modified
     */
    protected $_code = 304;

}

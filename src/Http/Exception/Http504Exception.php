<?php

namespace tourze\Http\Exception;

class Http504Exception extends ExpectedException
{

    /**
     * @var   int    HTTP 504 Gateway Timeout
     */
    protected $_code = 504;

}

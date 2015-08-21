<?php

namespace tourze\Http\Exception;

class Http300Exception extends RedirectException
{

    /**
     * @var   int    HTTP 300 Multiple Choices
     */
    protected $_code = 300;

}

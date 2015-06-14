<?php

namespace tourze\Http\Exception;

class Http300Exception extends RedirectException
{

    /**
     * @var   integer    HTTP 300 Multiple Choices
     */
    protected $_code = 300;

}

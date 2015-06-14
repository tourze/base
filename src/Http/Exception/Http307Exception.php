<?php

namespace tourze\Http\Exception;

class Http307Exception extends RedirectException
{

    /**
     * @var   integer    HTTP 307 Temporary Redirect
     */
    protected $_code = 307;

}

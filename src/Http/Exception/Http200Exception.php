<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http200Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::OK;

}

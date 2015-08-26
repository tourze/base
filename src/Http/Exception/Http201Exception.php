<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http201Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::CREATED;

}

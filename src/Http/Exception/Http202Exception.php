<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http202Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::ACCEPTED;

}

<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http205Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::RESET_CONTENT;

}

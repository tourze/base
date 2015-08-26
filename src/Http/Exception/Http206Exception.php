<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http206Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::PARTIAL_CONTENT;

}

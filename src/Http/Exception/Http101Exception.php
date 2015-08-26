<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http101Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::SWITCHING_PROTOCOLS;

}

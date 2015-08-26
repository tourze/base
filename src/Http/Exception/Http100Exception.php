<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http100Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::CONTINUES;

}

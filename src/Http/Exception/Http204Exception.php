<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http204Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::NO_CONTENT;

}

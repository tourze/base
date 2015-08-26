<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http203Exception extends RedirectException
{

    /**
     * @var int
     */
    protected $_code = Message::NON_AUTHORITATIVE_INFORMATION;

}

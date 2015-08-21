<?php

namespace tourze\Http\Exception;

use tourze\Http\Message;

class Http201Exception extends RedirectException
{

    /**
     * @var   int    HTTP 201 Created
     */
    protected $_code = Message::CREATED;

}

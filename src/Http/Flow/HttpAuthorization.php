<?php

namespace tourze\Http\Flow;

use tourze\Base\Flow\FlowHandlerInterface;
use tourze\Base\Flow\FlowLayer;

/**
 * HTTP授权
 *
 * @package tourze\Mvc\Flow
 */
class HttpAuthorization extends FlowLayer implements FlowHandlerInterface
{

    /**
     * 每个请求层，最终被调用的方法
     *
     * @return mixed
     */
    public function handle()
    {
    }
}

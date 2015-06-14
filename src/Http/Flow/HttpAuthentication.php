<?php

namespace tourze\Http\Flow;

use tourze\Base\Flow\FlowHandlerInterface;
use tourze\Base\Flow\FlowLayer;

/**
 * HTTP认证
 *
 * @package tourze\Mvc\Flow
 */
class HttpAuthentication extends FlowLayer implements FlowHandlerInterface
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

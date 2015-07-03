<?php

namespace tourze\Bootstrap\Flow;

use tourze\Flow\Flow;
use tourze\Flow\HandlerInterface;
use tourze\Flow\Layer;
use tourze\Http\Request;

/**
 * HTTP请求和处理流
 *
 * @package tourze\Mvc\Flow
 */
class Http extends Layer implements HandlerInterface
{

    /**
     * 每个请求层，最终被调用的方法
     *
     * @return mixed
     */
    public function handle()
    {
        $request = new Request($this->flow->contexts['uri']);

        // 上下文
        $this->flow->contexts['request'] = $request;

        // 处理HTTP相关，例如过滤变量，初始化相关设置
        $flow = Flow::instance('tourze-http');
        $flow->contexts =& $this->flow->contexts;
        $flow->layers = [
            'tourze\Bootstrap\Flow\Http\Init', // HTTP初始化
            'tourze\Bootstrap\Flow\Http\Authentication', // HTTP认证
            'tourze\Bootstrap\Flow\Http\Authorization',  // HTTP授权
        ];
        $flow->start();

        // 执行请求
        $response = $request->execute();
        /**
         * 执行主请求
         */
        echo $response
            ->sendHeaders(true)
            ->body;
    }
}

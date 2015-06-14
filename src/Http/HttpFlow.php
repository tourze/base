<?php

namespace tourze\Http;

use tourze\Base\Flow;
use tourze\Base\Flow\FlowHandlerInterface;
use tourze\Base\Flow\FlowLayer;

/**
 * HTTP请求和处理流
 *
 * @package tourze\Mvc\Flow
 */
class HttpFlow extends FlowLayer implements FlowHandlerInterface
{

    /**
     * 每个请求层，最终被调用的方法
     *
     * @return mixed
     */
    public function handle()
    {
        $request = new HttpRequest($this->flow->contexts['uri']);

        // 上下文
        $this->flow->contexts['request'] = $request;

        //var_dump($request->isInitial());
        //echo "httpFlow: " . spl_object_hash($request) . "<br>\n";

        // 处理HTTP相关，例如过滤变量，初始化相关设置
        $flow = Flow::instance('sdk-http');
        $flow->contexts =& $this->flow->contexts;
        $flow->layers = [
            'tourze\Http\Flow\HttpInit', // HTTP初始化
            'tourze\Http\Flow\HttpAuthentication', // HTTP认证
            'tourze\Http\Flow\HttpAuthorization',  // HTTP授权
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

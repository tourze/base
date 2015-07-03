<?php

namespace tourze\Flow;

/**
 * handle实现接口
 *
 * @package tourze\Flow
 */
interface HandlerInterface
{

    /**
     * 每个请求层，最终被调用的方法
     *
     * @return mixed
     */
    public function handle();
}

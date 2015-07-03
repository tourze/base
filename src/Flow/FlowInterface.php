<?php

namespace tourze\Flow;

/**
 * 通用的Flow层接口
 *
 * @package tourze\Flow
 */
interface FlowInterface
{

    /**
     * 开始执行请求
     *
     * @return mixed
     */
    public function start();

    /**
     * 停止流程
     */
    public function stop();

    /**
     * 暂停流程
     */
    public function pause();

    /**
     * 恢复流程
     */
    public function resume();
}

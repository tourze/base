<?php

namespace tourze\Base\Flow;

use tourze\Base\Flow;

interface FlowLayerInterface
{

    /**
     * 传递当前的请求流
     *
     * @param Flow $flow
     *
     * @return mixed
     */
    public function setFlow(Flow $flow);

    /**
     * 获取当前层所在的请求流
     *
     * @return Flow
     */
    public function getFlow();

}

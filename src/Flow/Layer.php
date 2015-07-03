<?php

namespace tourze\Flow;

use tourze\Base\Object;

/**
 * Flow分层
 *
 * @property  Flow  flow
 */
class Layer extends Object implements LayerInterface
{

    /**
     * @var Flow 当前所处在的请求层
     */
    protected $_flow;

    /**
     * 传递当前的请求流
     *
     * @param Flow $flow
     *
     * @return mixed
     */
    public function setFlow(Flow $flow)
    {
        $this->_flow = $flow;
    }

    /**
     * 获取当前层所在的请求流
     *
     * @return Flow
     */
    public function getFlow()
    {
        return $this->_flow;
    }
}

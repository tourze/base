<?php

namespace tourze\Flow;

use tourze\Base\Object;

/**
 * 基础的请求控制类，用于控制执行的请求流
 * Flow常用于耦合多个模块，合并为单次请求
 *
 * $requestFlow = Flow::instance('request')
 *     ->addLayer([
 *         '\request\RequestInit',
 *         '\request\RequestSession',
 *         '\request\RequestAuth',
 *         '\request\RequestExec',
 *         '\request\RequestFetch',
 *     ]);
 * $requestFlow->run();
 *
 * @property mixed              layers
 * @property LayerInterface previousLayer
 * @property LayerInterface nextLayer
 * @property mixed              previousLayerResult
 */
class Flow extends Object implements FlowInterface
{

    /**
     * @var array  当前请求流实用到的层
     */
    protected $_layers = [];

    /**
     * @var  LayerInterface  上一个执行的层
     */
    protected $_previousLayer;

    /**
     * @var  LayerInterface  下一个执行的层
     */
    protected $_nextLayer;

    /**
     * @var  mixed 上一层的执行结果
     */
    protected $_previousLayerResult;

    /**
     * @var array  上下文信息
     */
    public $contexts = [];

    /**
     * 开始流程
     */
    public function start()
    {
        foreach ($this->layers as $layer)
        {
            // 数组格式，可以传参
            if (is_array($layer))
            {

            }

            // 如果是字符串
            if (is_string($layer))
            {
                $layer = new $layer;
            }

            if ($layer instanceof LayerInterface && $layer instanceof HandlerInterface)
            {
                // 传递当前请求流
                $layer->setFlow($this);
                // 执行请求
                $this->_previousLayerResult = $layer->handle();
            }
        }
    }

    /**
     * 停止流程
     */
    public function stop()
    {
    }

    /**
     * 暂停流程
     */
    public function pause()
    {

    }

    /**
     * 恢复流程
     */
    public function resume()
    {
        // TODO: Implement resume() method.
    }

    /**
     * 增加执行请求层
     *
     * @param $layers
     */
    public function addLayer($layers)
    {
        if ( ! is_array($layers))
        {
            $layers = [$layers];
        }
        foreach ($layers as $layer)
        {
            if ( ! in_array($layer, $this->layers))
            {
                $this->layers[] = $layer;
            }
        }
    }

    /**
     * @return array
     */
    public function getLayers()
    {
        return $this->_layers;
    }

    /**
     * @param array $layers
     */
    public function setLayers($layers)
    {
        $this->_layers = $layers;
    }

    /**
     * @return LayerInterface
     */
    public function getPreviousLayer()
    {
        return $this->_previousLayer;
    }

    /**
     * @param LayerInterface $previousLayer
     */
    public function setPreviousLayer($previousLayer)
    {
        $this->_previousLayer = $previousLayer;
    }

    /**
     * @return LayerInterface
     */
    public function getNextLayer()
    {
        return $this->_nextLayer;
    }

    /**
     * @param LayerInterface $nextLayer
     */
    public function setNextLayer($nextLayer)
    {
        $this->_nextLayer = $nextLayer;
    }

    /**
     * @return boolean
     */
    public function getPreviousLayerResult()
    {
        return $this->_previousLayerResult;
    }

    /**
     * @param boolean $previousLayerResult
     */
    public function setPreviousLayerResult($previousLayerResult)
    {
        $this->_previousLayerResult = $previousLayerResult;
    }
}

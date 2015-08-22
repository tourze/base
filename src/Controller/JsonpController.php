<?php

namespace tourze\Controller;

use tourze\Controller\Exception\JsonpInvalidParameterException;

/**
 * JSONP控制器
 *
 * @package tourze\Controller
 */
abstract class JsonpController extends WebController
{

    /**
     * @var string callback字符串
     */
    public $callbackParam = 'callback';

    /**
     * @var bool 自动降级标记，当没有callback时，自动调整为json方式
     */
    public $autoSink = false;

    /**
     * @inheritdoc
     */
    public function executeAction()
    {
        if ( ! ($callback = $this->request->query($this->callbackParam)) && ! $this->autoSink)
        {
            throw new JsonpInvalidParameterException('The required parameter ":param" not found.', [
                ':param' => $this->callbackParam
            ]);
        }

        // 继续执行
        parent::executeAction();

        $this->response->headers('content-type', 'application/json');
        $this->response->body = $this->formatContent($callback, (array) $this->actionResult);
    }

    /**
     * 格式化输出
     *
     * @param string $callback
     * @param mixed  $result
     * @return string
     */
    protected function formatContent($callback, $result)
    {
        if ( ! $callback && $this->autoSink)
        {
            return json_encode($result);
        }
        return $callback . '(' . json_encode($result) . ')';
    }

}

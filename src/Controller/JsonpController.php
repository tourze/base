<?php

namespace tourze\Controller;

use tourze\Controller\Exception\JsonpInvalidParameterException;

/**
 * JSONP控制器
 *
 * @package tourze\Controller
 */
abstract class JsonpController extends Controller
{

    /**
     * @var string callback字符串
     */
    public $callbackParam = 'callback';



    /**
     * @inheritdoc
     */
    public function executeAction()
    {
        if ( ! $callback = $this->request->query($this->callbackParam))
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
     * @param $callback
     * @param $result
     * @return string
     */
    protected function formatContent($callback, $result)
    {
        return $callback . '(' .json_encode($result) . ')';
    }

}

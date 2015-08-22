<?php

namespace tourze\Controller;

/**
 * JSON控制器
 *
 * @package tourze\Controller
 */
abstract class JsonController extends WebController
{

    /**
     * @inheritdoc
     */
    public function executeAction()
    {
        // 继续执行
        parent::executeAction();

        $this->response->headers('content-type', 'application/json');
        $this->response->body = json_encode($this->actionResult);
    }

}

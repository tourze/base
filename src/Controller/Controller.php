<?php

namespace tourze\Controller;

use tourze\Base\Object;
use tourze\Http\Exception\Http404Exception;
use tourze\Http\Exception\HttpException;
use tourze\Http\Message;
use tourze\Http\Response;
use tourze\Http\Request;

/**
 * 控制器基础类，请求流程大概为：
 *
 *     $controller = new FooController($request);
 *     $controller->before();
 *     $controller->actionBar();
 *     $controller->after();
 *
 * @property  Request  request
 * @property  Response response
 * @property  mixed    actionResult
 * @package    Base
 * @category   Controller
 * @author     YwiSax
 */
abstract class Controller extends Object
{

    /**
     * @var  Request  创建控制器实例的请求
     */
    public $_request;

    /**
     * @var  Response  用于返回控制器执行结果的响应实例
     */
    public $_response;

    /**
     * @var mixed action的执行结果
     */
    public $_actionResult;

    /**
     * @var boolean 标志位，是否停止执行
     */
    public $break = false;

    /**
     * 开始处理请求
     *
     * @throws HttpException
     * @throws Http404Exception
     * @return Response
     */
    public function execute()
    {
        if ( ! $this->break)
        {
            $this->executeBefore();
        }
        if ( ! $this->break)
        {
            $this->executeAction();
        }
        if ( ! $this->break)
        {
            $this->executeAfter();
        }

        // Return the response
        return $this->response;
    }

    /**
     * 执行action
     *
     * @throws HttpException
     */
    public function executeAction()
    {
        $actionSign = '';
        foreach (explode('-', $this->request->action) as $part)
        {
            $actionSign .= ucfirst($part);
        }

        $actions = [
            'action' . $actionSign
        ];

        $matchAction = false;
        foreach ($actions as $action)
        {
            if (method_exists($this, $action))
            {
                $matchAction = $action;
                break;
            }
        }

        // 检查对应的方法是否存在
        if ( ! $matchAction)
        {
            $this->missingAction();
        }

        // 保存结果
        $this->actionResult = $this->{$matchAction}();
    }

    /**
     * 动作不存在时的操作
     *
     * @throws \tourze\Http\Exception\HttpException
     */
    public function missingAction()
    {
        throw HttpException::factory(Message::NOT_FOUND, 'The requested URL :uri was not found on this server.', [
            ':uri' => $this->request->uri
        ])->request($this->request);
    }

    /**
     * 执行action前的操作，可以做预备操作
     *
     * @return  void
     */
    public function before()
    {
    }

    public function executeBefore()
    {
        $this->before();
    }

    /**
     * 执行action后的操作，可以用来做收尾工作
     *
     * @return  void
     */
    public function after()
    {
    }

    public function executeAfter()
    {
        $this->after();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->_request = $request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->_response = $response;
    }

    /**
     * @return mixed
     */
    public function getActionResult()
    {
        return $this->_actionResult;
    }

    /**
     * @param mixed $actionResult
     */
    public function setActionResult($actionResult)
    {
        $this->_actionResult = $actionResult;
    }
}

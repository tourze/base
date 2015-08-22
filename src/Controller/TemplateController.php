<?php

namespace tourze\Controller;

use tourze\View\View;
use tourze\View\ViewInterface;

/**
 * 最基础的模板控制器，实现页面布局分离功能
 *
 * @package    Base
 * @category   Controller
 * @author     YwiSax
 */
abstract class TemplateController extends WebController
{

    /**
     * @var  string|View  模板名，或者模板对象
     */
    protected $template = 'template';

    /**
     * @var  boolean  是否自动加载模板
     **/
    public $autoRender = true;

    /**
     * 初始化，并加载模板对象
     */
    public function before()
    {
        parent::before();

        if (true === $this->autoRender)
        {
            if ( ! $this->template instanceof ViewInterface)
            {
                $this->template = View::factory($this->template);
            }
        }
    }

    /**
     * 完成模板渲染，并输出
     */
    public function after()
    {
        if (true === $this->autoRender)
        {
            $this->response->body = $this->template->render();
        }
        parent::after();
    }

}

<?php

namespace tourze\Controller;

use tourze\View\View;

/**
 * 最基础的模板控制器，实现页面布局分离功能
 *
 * @package    Base
 * @category   Controller
 * @author     YwiSax
 */
abstract class TemplateController extends BaseController
{

    /**
     * @var  View  page template
     */
    protected $template = 'template';

    /**
     * @var  boolean  auto render template
     **/
    public $autoRender = true;

    /**
     * Loads the template [View] object.
     */
    public function before()
    {
        parent::before();

        if (true === $this->autoRender)
        {
            // Load the template
            $this->template = View::factory($this->template);
        }
    }

    /**
     * Assigns the template [View] as the request response.
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

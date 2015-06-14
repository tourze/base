<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 按钮
 *
 * @property mixed autoFocus       规定当页面加载时按钮应当自动地获得焦点
 * @property mixed disabled        规定应该禁用该按钮
 * @property mixed form            规定按钮属于一个或多个表单
 * @property mixed formAction      覆盖form元素的action属性，该属性与`type="submit"`配合使用
 * @property mixed formEncType     覆盖form元素的enctype属性，该属性与`type="submit"`配合使用
 * @property mixed formMethod      覆盖form元素的method属性，该属性与`type="submit"`配合使用
 * @property mixed formNovalidate  覆盖form元素的novalidate属性，该属性与`type="submit"`配合使用
 * @property mixed formTarget      覆盖form元素的target属性，该属性与`type="submit"`配合使用
 * @property mixed name            规定按钮的名称
 * @property mixed type            规定按钮的类型
 * @property mixed value           规定按钮的初始值。可由脚本进行修改
 */
class Button extends Tag implements InlineElement
{

    protected $_tagName = 'button';

    /**
     * @return null|string|array
     */
    public function getAutoFocus()
    {
        return $this->getAttribute('autoFocus');
    }

    /**
     * @param $autoFocus
     */
    public function setAutoFocus($autoFocus)
    {
        $this->setAttribute('autoFocus', $autoFocus);
    }

    /**
     * @return null|string|array
     */
    public function getDisabled()
    {
        return $this->getAttribute('disabled');
    }

    /**
     * @param $disabled
     */
    public function setDisabled($disabled)
    {
        $this->setAttribute('disabled', $disabled);
    }

    /**
     * @return null|string|array
     */
    public function getForm()
    {
        return $this->getAttribute('form');
    }

    /**
     * @param $form
     */
    public function setForm($form)
    {
        $this->setAttribute('form', $form);
    }

    /**
     * @return null|string|array
     */
    public function getFormAction()
    {
        return $this->getAttribute('formAction');
    }

    /**
     * @param $formAction
     */
    public function setFormAction($formAction)
    {
        $this->setAttribute('formAction', $formAction);
    }

    /**
     * @return null|string|array
     */
    public function getFormEncType()
    {
        return $this->getAttribute('formEncType');
    }

    /**
     * @param $formEncType
     */
    public function setFormEncType($formEncType)
    {
        $this->setAttribute('formEncType', $formEncType);
    }

    /**
     * @return null|string|array
     */
    public function getFormMethod()
    {
        return $this->getAttribute('formMethod');
    }

    /**
     * @param $formMethod
     */
    public function setFormMethod($formMethod)
    {
        $this->setAttribute('formMethod', $formMethod);
    }

    /**
     * @return null|string|array
     */
    public function getFormNovalidate()
    {
        return $this->getAttribute('formNovalidate');
    }

    /**
     * @param $formNovalidate
     */
    public function setFormNovalidate($formNovalidate)
    {
        $this->setAttribute('formNovalidate', $formNovalidate);
    }

    /**
     * @return null|string|array
     */
    public function getFormTarget()
    {
        return $this->getAttribute('formTarget');
    }

    /**
     * @param $formTarget
     */
    public function setFormTarget($formTarget)
    {
        $this->setAttribute('formTarget', $formTarget);
    }

    /**
     * @return null|string|array
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @param name
     */
    public function setName($name)
    {
        $this->setAttribute('name', $name);
    }

    /**
     * @return null|string|array
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param type
     */
    public function setType($type)
    {
        $this->setAttribute('type', $type);
    }

    /**
     * @return null|string|array
     */
    public function getValue()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->setAttribute('type', $value);
    }

}

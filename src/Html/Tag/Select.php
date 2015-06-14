<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * select元素可创建单选或多选菜单
 *
 * @property mixed autoFocus  规定在页面加载后文本区域自动获得焦点
 * @property mixed disabled   规定禁用该下拉列表
 * @property mixed form       规定文本区域所属的一个或多个表单
 * @property mixed multiple   规定可选择多个选项
 * @property mixed name       规定下拉列表的名称
 * @property mixed required   规定文本区域是必填的
 * @property mixed size       规定下拉列表中可见选项的数目
 */
class Select extends Tag implements InlineElement
{

    protected $_tagName = 'select';

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
    public function getMultiple()
    {
        return $this->getAttribute('multiple');
    }

    /**
     * @param $multiple
     */
    public function setMultiple($multiple)
    {
        $this->setAttribute('multiple', $multiple);
    }

    /**
     * @return null|string|array
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->setAttribute('name', $name);
    }

    /**
     * @return null|string|array
     */
    public function getRequired()
    {
        return $this->getAttribute('required');
    }

    /**
     * @param $required
     */
    public function setRequired($required)
    {
        $this->setAttribute('required', $required);
    }

    /**
     * @return null|string|array
     */
    public function getSize()
    {
        return $this->getAttribute('size');
    }

    /**
     * @param $size
     */
    public function setSize($size)
    {
        $this->setAttribute('size', $size);
    }

}

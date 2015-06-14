<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 定义多行的文本输入控件
 *
 * @property mixed autoFocus    规定在页面加载后文本区域自动获得焦点
 * @property mixed cols         规定文本区内的可见宽度
 * @property mixed disabled     规定禁用该下拉列表
 * @property mixed form         规定文本区域所属的一个或多个表单
 * @property mixed maxLength    规定文本区域的最大字符数
 * @property mixed name         规定文本区的名称
 * @property mixed placeholder  规定描述文本区域预期值的简短提示
 * @property mixed readonly     规定文本区为只读
 * @property mixed required     规定文本区域是必填的
 * @property mixed rows         规定文本区内的可见行数
 * @property mixed wrap         规定当在表单中提交时，文本区域中的文本如何换行
 */
class TextArea extends Tag implements InlineElement
{

    protected $_tagName = 'textarea';

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
    public function getCols()
    {
        return $this->getAttribute('cols');
    }

    /**
     * @param $cols
     */
    public function setCols($cols)
    {
        $this->setAttribute('cols', $cols);
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
    public function getMaxLength()
    {
        return $this->getAttribute('maxLength');
    }

    /**
     * @param $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->setAttribute('maxLength', $maxLength);
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
    public function getPlaceholder()
    {
        return $this->getAttribute('placeholder');
    }

    /**
     * @param $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->setAttribute('placeholder', $placeholder);
    }

    /**
     * @return null|string|array
     */
    public function getReadonly()
    {
        return $this->getAttribute('readonly');
    }

    /**
     * @param $readonly
     */
    public function setReadonly($readonly)
    {
        $this->setAttribute('readonly', $readonly);
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
    public function getRows()
    {
        return $this->getAttribute('rows');
    }

    /**
     * @param $rows
     */
    public function setRows($rows)
    {
        $this->setAttribute('rows', $rows);
    }

    /**
     * @return null|string|array
     */
    public function getWrap()
    {
        return $this->getAttribute('wrap');
    }

    /**
     * @param $wrap
     */
    public function setWrap($wrap)
    {
        $this->setAttribute('wrap', $wrap);
    }

}

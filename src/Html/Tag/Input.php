<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 定义多行的文本输入控件
 *
 * @property mixed accept          规定通过文件上传来提交的文件的类型
 * @property mixed alt             定义图像输入的替代文本
 * @property mixed autoComplete    规定是否使用输入字段的自动完成功能
 * @property mixed autoFocus       规定在页面加载后文本区域自动获得焦点
 * @property mixed checked         规定此input元素首次加载时应当被选中
 * @property mixed disabled        当input元素加载时禁用此元素
 * @property mixed form            规定文本区域所属的一个或多个表单
 * @property mixed formAction      覆盖表单的action属性，（适用于 type="submit" 和 type="image"）
 * @property mixed formEncType     覆盖表单的enctype属性，（适用于 type="submit" 和 type="image"）
 * @property mixed formMethod      覆盖表单的method属性，（适用于 type="submit" 和 type="image"）
 * @property mixed formNovalidate  覆盖表单的novalidate属性，（适用于 type="submit" 和 type="image"）
 * @property mixed formTarget      覆盖表单的target属性，（适用于 type="submit" 和 type="image"）
 * @property mixed height          定义input字段的高度。（适用于 type="image"）
 * @property mixed list            引用包含输入字段的预定义选项的data list
 * @property mixed max             规定输入字段的最大值，请与 "min" 属性配合使用，来创建合法值的范围
 * @property mixed maxLength       规定文本区域的最大字符数
 * @property mixed min             规定输入字段的最小值，请与 "max" 属性配合使用，来创建合法值的范围
 * @property mixed multiple        如果使用该属性，则允许一个以上的值
 * @property mixed name            规定文本区的名称
 * @property mixed pattern         规定输入字段的值的模式或格式，例如 pattern="[0-9]"表示输入值必须是0与9之间的数字
 * @property mixed placeholder     规定描述文本区域预期值的简短提示
 * @property mixed readonly        规定文本区为只读
 * @property mixed required        规定文本区域是必填的
 * @property mixed size            定义输入字段的宽度
 * @property mixed src             定义以提交按钮形式显示的图像的URL
 * @property mixed step            规定输入字的的合法数字间隔
 * @property mixed type            规定input元素的类型
 * @property mixed value           规定input元素的值
 * @property mixed width           定义input字段的宽度。（适用于 type="image"）
 */
class Input extends Tag implements InlineElement
{

    protected $_tagName = 'input';

    protected $_tagClosed = false;

    /**
     * @return null|string|array
     */
    public function getAccept()
    {
        return $this->getAttribute('accept');
    }

    /**
     * @param $accept
     */
    public function setAccept($accept)
    {
        $this->setAttribute('accept', $accept);
    }

    /**
     * @return null|string|array
     */
    public function getAlt()
    {
        return $this->getAttribute('alt');
    }

    /**
     * @param $alt
     */
    public function setAlt($alt)
    {
        $this->setAttribute('alt', $alt);
    }

    /**
     * @return null|string|array
     */
    public function getAutoComplete()
    {
        return $this->getAttribute('autoComplete');
    }

    /**
     * @param $autoComplete
     */
    public function setAutoComplete($autoComplete)
    {
        $this->setAttribute('autoComplete', $autoComplete);
    }

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
    public function getChecked()
    {
        return $this->getAttribute('checked');
    }

    /**
     * @param $checked
     */
    public function setChecked($checked)
    {
        $this->setAttribute('checked', $checked);
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
    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    /**
     * @param $height
     */
    public function setHeight($height)
    {
        $this->setAttribute('height', $height);
    }

    /**
     * @return null|string|array
     */
    public function getList()
    {
        return $this->getAttribute('height');
    }

    /**
     * @param $list
     */
    public function setList($list)
    {
        $this->setAttribute('list', $list);
    }

    /**
     * @return null|string|array
     */
    public function getMax()
    {
        return $this->getAttribute('max');
    }

    /**
     * @param $max
     */
    public function setMax($max)
    {
        $this->setAttribute('max', $max);
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
    public function getMin()
    {
        return $this->getAttribute('min');
    }

    /**
     * @param $min
     */
    public function setMin($min)
    {
        $this->setAttribute('min', $min);
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
    public function getPattern()
    {
        return $this->getAttribute('pattern');
    }

    /**
     * @param $pattern
     */
    public function setPattern($pattern)
    {
        $this->setAttribute('pattern', $pattern);
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

    /**
     * @return null|string|array
     */
    public function getSrc()
    {
        return $this->getAttribute('src');
    }

    /**
     * @param $src
     */
    public function setSrc($src)
    {
        $this->setAttribute('src', $src);
    }

    /**
     * @return null|string|array
     */
    public function getStep()
    {
        return $this->getAttribute('step');
    }

    /**
     * @param $step
     */
    public function setStep($step)
    {
        $this->setAttribute('step', $step);
    }

    /**
     * @return null|string|array
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param $type
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
        return $this->getAttribute('value');
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->setAttribute('value', $value);
    }

    /**
     * @return null|string|array
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * @param $width
     */
    public function setWidth($width)
    {
        $this->setAttribute('width', $width);
    }

}

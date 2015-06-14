<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * form标签
 *
 * @property mixed acceptCharset 规定服务器可处理的表单数据字符集
 * @property mixed action        规定当提交表单时向何处发送表单数据
 * @property mixed autoComplete  规定是否启用表单的自动完成功能
 * @property mixed encType       规定在发送表单数据之前如何对其进行编码
 * @property mixed method        规定用于发送 form-data 的 HTTP 方法
 * @property mixed name          规定表单的名称
 * @property mixed noValidate    如果使用该属性，则提交表单时不进行验证
 * @property mixed target        规定在何处打开 action URL
 */
class Form extends Tag implements BlockElement
{

    /**
     * @return null|string|array
     */
    public function getAcceptCharset()
    {
        return $this->getAttribute('acceptCharset');
    }

    /**
     * @param $acceptCharset
     */
    public function setAcceptCharset($acceptCharset)
    {
        $this->setAttribute('acceptCharset', $acceptCharset);
    }

    /**
     * @return null|string|array
     */
    public function getAction()
    {
        return $this->getAttribute('action');
    }

    /**
     * @param $action
     */
    public function setAction($action)
    {
        $this->setAttribute('action', $action);
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
    public function getEncType()
    {
        return $this->getAttribute('encType');
    }

    /**
     * @param $encType
     */
    public function setEncType($encType)
    {
        $this->setAttribute('encType', $encType);
    }

    /**
     * @return null|string|array
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }

    /**
     * @param $method
     */
    public function setMethod($method)
    {
        $this->setAttribute('method', $method);
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
    public function getNoValidate()
    {
        return $this->getAttribute('noValidate');
    }

    /**
     * @param $noValidate
     */
    public function setNoValidate($noValidate)
    {
        $this->setAttribute('noValidate', $noValidate);
    }

    /**
     * @return null|string|array
     */
    public function getTarget()
    {
        return $this->getAttribute('target');
    }

    /**
     * @param $target
     */
    public function setTarget($target)
    {
        $this->setAttribute('target', $target);
    }

}

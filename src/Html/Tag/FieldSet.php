<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * fieldset 元素可将表单内的相关元素分组。
 *
 * @package tourze\Html\Tag
 *
 * @property mixed disabled  规定应该禁用 fieldset。
 * @property mixed form      规定 fieldset 所属的一个或多个表单。
 * @property mixed name      规定 fieldset 的名称。
 */
class FieldSet extends Tag implements BlockElement
{

    protected $_tagName = 'fieldset';

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
}

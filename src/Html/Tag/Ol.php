<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * 有序列表
 *
 * @package tourze\Html\Tag
 *
 * @property mixed reversed 规定列表顺序为降序。(9,8,7...)
 * @property mixed start    规定有序列表的起始值
 * @property mixed type     规定在列表中使用的标记类型
 */
class Ol extends Tag implements BlockElement
{

    protected $_tagName = 'ol';

    /**
     * @return null|string|array
     */
    public function getReversed()
    {
        return $this->getAttribute('reversed');
    }

    /**
     * @param $reversed
     */
    public function setReversed($reversed)
    {
        $this->setAttribute('reversed', $reversed);
    }

    /**
     * @return null|string|array
     */
    public function getStart()
    {
        return $this->getAttribute('start');
    }

    /**
     * @param $start
     */
    public function setStart($start)
    {
        $this->setAttribute('start', $start);
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

}

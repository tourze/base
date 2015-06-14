<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * FrameSet
 *
 * @property mixed cols  定义框架集中列的数目和尺寸。
 * @property mixed rows  定义框架集中行的数目和尺寸
 */
class FrameSet extends Tag implements TopElement
{

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

}

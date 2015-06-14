<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * HTML表格
 *
 * @property mixed border      规定表格边框的宽度
 * @property mixed cellPadding 规定单元边沿与其内容之间的空白
 * @property mixed cellSpacing 规定单元格之间的空白
 * @property mixed frame       规定外侧边框的哪个部分是可见的
 * @property mixed rules       规定内侧边框的哪个部分是可见的
 * @property mixed summary     规定表格的摘要
 * @property mixed width       规定表格的宽度
 */
class Table extends Tag implements BlockElement
{

    protected $_tagName = 'table';

    /**
     * @return null|string|array
     */
    public function getBorder()
    {
        return $this->getAttribute('border');
    }

    /**
     * @param $border
     */
    public function setBorder($border)
    {
        $this->setAttribute('border', $border);
    }

    /**
     * @return null|string|array
     */
    public function getCellPadding()
    {
        return $this->getAttribute('cellPadding');
    }

    /**
     * @param $cellPadding
     */
    public function setCellPadding($cellPadding)
    {
        $this->setAttribute('cellPadding', $cellPadding);
    }

    /**
     * @return null|string|array
     */
    public function getCellSpacing()
    {
        return $this->getAttribute('cellSpacing');
    }

    /**
     * @param $cellSpacing
     */
    public function setCellSpacing($cellSpacing)
    {
        $this->setAttribute('cellSpacing', $cellSpacing);
    }

    /**
     * @return null|string|array
     */
    public function getFrame()
    {
        return $this->getAttribute('frame');
    }

    /**
     * @param $frame
     */
    public function setFrame($frame)
    {
        $this->setAttribute('frame', $frame);
    }

    /**
     * @return null|string|array
     */
    public function getRules()
    {
        return $this->getAttribute('rules');
    }

    /**
     * @param $rules
     */
    public function setRules($rules)
    {
        $this->setAttribute('rules', $rules);
    }

    /**
     * @return null|string|array
     */
    public function getSummary()
    {
        return $this->getAttribute('summary');
    }

    /**
     * @param $summary
     */
    public function setSummary($summary)
    {
        $this->setAttribute('summary', $summary);
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

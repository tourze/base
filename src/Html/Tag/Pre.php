<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * pre 元素可定义预格式化的文本。被包围在 pre 元素中的文本通常会保留空格和换行符。而文本也会呈现为等宽字体
 *
 * @property mixed width 定义每行的最大字符数（通常是 40、80 或 132）。
 */
class Pre extends Tag implements BlockElement
{

    protected $_tagName = 'pre';

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

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 标签定义图形，比如图表和其他图像；标签只是图形容器，您必须使用脚本来绘制图形
 *
 * @property mixed height  设置canvas的高度
 * @property mixed width   设置canvas的宽度
 */
class Canvas extends Tag implements InlineElement
{

    protected $_tagName = 'canvas';

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

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * <embed>标签是HTML5中的新标签
 *
 * @property mixed height  设置嵌入内容的高度
 * @property mixed src     嵌入内容的URL
 * @property mixed type    定义嵌入内容的类型
 * @property mixed width   设置嵌入内容的宽度
 */
class Embed extends Tag implements BlockElement
{

    protected $_tagName = 'embed';

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

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * img 元素向网页中嵌入一幅图像
 *
 * @property mixed alt      规定图像的替代文本
 * @property mixed src      规定显示图像的 URL
 * @property mixed height   定义图像的高度
 * @property mixed isMap    将图像定义为服务器端图像映射
 * @property mixed longDesc 指向包含长的图像描述文档的 URL
 * @property mixed useMap   将图像定义为客户器端图像映射
 * @property mixed width    设置图像的宽度
 */
class Img extends Tag implements InlineElement
{

    protected $_tagName = 'img';

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
    public function getIsMap()
    {
        return $this->getAttribute('isMap');
    }

    /**
     * @param $isMap
     */
    public function setIsMap($isMap)
    {
        $this->setAttribute('isMap', $isMap);
    }

    /**
     * @return null|string|array
     */
    public function getLongDesc()
    {
        return $this->getAttribute('longDesc');
    }

    /**
     * @param $longDesc
     */
    public function setLongDesc($longDesc)
    {
        $this->setAttribute('longDesc', $longDesc);
    }

    /**
     * @return null|string|array
     */
    public function getUseMap()
    {
        return $this->getAttribute('useMap');
    }

    /**
     * @param $useMap
     */
    public function setUseMap($useMap)
    {
        $this->setAttribute('useMap', $useMap);
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

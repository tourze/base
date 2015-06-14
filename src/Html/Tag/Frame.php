<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * <frame> 标签定义 frameset 中的一个特定的窗口（框架）。
 * frameset 中的每个框架都可以设置不同的属性，比如 border、scrolling、noresize 等等。
 *
 * @property mixed frameBorder   规定是否显示框架周围的边框
 * @property mixed longDesc      规定一个包含有关框架内容的长描述的页面
 * @property mixed marginHeight  定义框架的上方和下方的边距
 * @property mixed marginWidth   定义框架的左侧和右侧的边距
 * @property mixed name          规定框架的名称
 * @property mixed noResize      规定无法调整框架的大小
 * @property mixed scrolling     规定是否在框架中显示滚动条
 * @property mixed src           规定在框架中显示的文档的URL
 */
class Frame extends Tag implements TopElement
{

    /**
     * @return null|string|array
     */
    public function getMarginHeight()
    {
        return $this->getAttribute('marginHeight');
    }

    /**
     * @param $marginHeight
     */
    public function setMarginHeight($marginHeight)
    {
        $this->setAttribute('marginHeight', $marginHeight);
    }

    /**
     * @return null|string|array
     */
    public function getMarginWidth()
    {
        return $this->getAttribute('marginWidth');
    }

    /**
     * @param $marginWidth
     */
    public function setMarginWidth($marginWidth)
    {
        $this->setAttribute('marginWidth', $marginWidth);
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
    public function getNoResize()
    {
        return $this->getAttribute('noResize');
    }

    /**
     * @param $noResize
     */
    public function setNoResize($noResize)
    {
        $this->setAttribute('noResize', $noResize);
    }

    /**
     * @return null|string|array
     */
    public function getScrolling()
    {
        return $this->getAttribute('scrolling');
    }

    /**
     * @param $scrolling
     */
    public function setScrolling($scrolling)
    {
        $this->setAttribute('scrolling', $scrolling);
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

}

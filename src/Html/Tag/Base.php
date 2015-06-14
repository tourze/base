<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * br标签
 *
 * @property mixed href    规定页面中所有相对链接的基准URL
 * @property mixed target  在何处打开页面中所有的链接
 */
class Base extends Tag implements TopElement
{

    protected $_tagName = 'base';

    /**
     * @var bool 是否成对标签
     */
    protected $_tagClosed = false;

    /**
     * @return null|string|array
     */
    public function getHref()
    {
        return $this->getAttribute('href');
    }

    /**
     * @param $href
     */
    public function setHref($href)
    {
        $this->setAttribute('href', $href);
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

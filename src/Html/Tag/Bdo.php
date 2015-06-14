<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * bdo 元素可覆盖默认的文本方向
 *
 * @property mixed dir  定义文字的方向
 */
class Bdo extends Tag implements InlineElement
{

    protected $_tagName = 'bdo';

    /**
     * @return null|string|array
     */
    public function getDir()
    {
        return $this->getAttribute('dir');
    }

    /**
     * @param $dir
     */
    public function setDir($dir)
    {
        $this->setAttribute('dir', $dir);
    }

}

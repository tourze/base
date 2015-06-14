<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * br标签
 */
class Br extends Tag implements InlineElement
{

    protected $_tagName = 'br';

    /**
     * @var bool 是否成对标签
     */
    protected $_tagClosed = false;

}

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * noscript 元素用来定义在脚本未被执行时的替代内容（文本）
 *
 * @package tourze\Html\Tag
 */
class NoScript extends Tag implements BlockElement
{

    protected $_tagName = 'noscript';

}

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * <samp> 标签表示一段用户应该对其没有什么其他解释的文本字符。要从正常的上下文抽取这些字符时，通常要用到这个标签
 */
class Samp extends Tag implements InlineElement
{

    protected $_tagName = 'samp';

}

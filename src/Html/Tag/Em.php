<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * <em> 标签告诉浏览器把其中的文本表示为强调的内容。对于所有浏览器来说，这意味着要把这段文字用斜体来显示
 */
class Em extends Tag implements InlineElement
{

    protected $_tagName = 'em';

}

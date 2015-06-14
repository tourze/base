<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * <strong> 标签和 <em> 标签一样，用于强调文本，但它强调的程度更强一些。
 */
class Strong extends Tag implements InlineElement
{

    protected $_tagName = 'strong';

}

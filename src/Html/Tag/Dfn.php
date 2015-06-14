<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 可标记那些对特殊术语或短语的定义
 */
class Dfn extends Tag implements InlineElement
{

    protected $_tagName = 'dfn';

}

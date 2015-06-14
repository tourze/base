<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * legend 元素为 fieldset 元素定义标题（caption）。
 */
class Legend extends Tag implements BlockElement
{

    protected $_tagName = 'legend';

}

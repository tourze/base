<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * <aside> 标签定义其所处内容之外的内容。aside的内容应该与附近的内容相关。
 */
class Aside extends Tag implements BlockElement
{

    protected $_tagName = 'aside';

}

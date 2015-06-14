<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * hr标签，HTML 页面中创建一条水平线
 *
 * @package tourze\Html\Tag
 */
class Hr extends Tag implements BlockElement
{

    protected $_tagName = 'hr';

    /**
     * @var bool 是否成对标签
     */
    protected $_tagClosed = false;

}

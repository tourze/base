<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * <header> 标签定义文档的页眉（介绍信息）
 */
class Header extends Tag implements BlockElement
{

    protected $_tagName = 'header';

}

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * address标签，定义文档或文章的作者/拥有者的联系信息。
 *
 * @package tourze\Html\Tag
 */
class Address extends Tag implements BlockElement
{

    protected $_tagName = 'address';

}

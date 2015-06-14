<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\BlockElement;
use tourze\Html\Tag;

/**
 * <article> 标签规定独立的自包含内容
 */
class Article extends Tag implements BlockElement
{

    protected $_tagName = 'article';

}

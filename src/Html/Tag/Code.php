<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 用于表示计算机源代码或者其他机器可以阅读的文本内容
 */
class Code extends Tag implements InlineElement
{

    protected $_tagName = 'code';

}

<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * 指示简称或缩写，比如 "WWW" 或 "NATO"
 */
class Abbr extends Tag implements InlineElement
{

    protected $_tagName = 'abbr';

}

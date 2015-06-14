<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * script标签
 *
 * @property mixed async    规定异步执行脚本（仅适用于外部脚本）
 * @property mixed charset  规定在外部脚本文件中使用的字符编码
 * @property mixed defer    规定是否对脚本执行进行延迟，直到页面加载为止
 * @property mixed src      规定外部脚本文件的 URL
 * @property mixed type     指示脚本的 MIME 类型
 */
class Script extends Tag implements TopElement
{

    protected $_tagName = 'script';

    public function init()
    {
        if ( ! $this->type)
        {
            $this->type = 'text/javascript';
        }
    }

    /**
     * @return null|string|array
     */
    public function getAsync()
    {
        return $this->getAttribute('async');
    }

    /**
     * @param $async
     */
    public function setAsync($async)
    {
        $this->setAttribute('async', $async);
    }

    /**
     * @return null|string|array
     */
    public function getCharset()
    {
        return $this->getAttribute('charset');
    }

    /**
     * @param $charset
     */
    public function setCharset($charset)
    {
        $this->setAttribute('charset', $charset);
    }

    /**
     * @return null|string|array
     */
    public function getDefer()
    {
        return $this->getAttribute('defer');
    }

    /**
     * @param $defer
     */
    public function setDefer($defer)
    {
        $this->setAttribute('defer', $defer);
    }

    /**
     * @return null|string|array
     */
    public function getSrc()
    {
        return $this->getAttribute('src');
    }

    /**
     * @param $src
     */
    public function setSrc($src)
    {
        $this->setAttribute('src', $src);
    }

    /**
     * @return null|string|array
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->setAttribute('type', $type);
    }
}

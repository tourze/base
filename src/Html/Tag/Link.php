<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * link-css标签
 *
 * @property mixed href     规定被链接文档的位置
 * @property mixed hrefLang 规定被链接文档中文本的语言
 * @property mixed media    规定被链接文档将被显示在什么设备上
 * @property mixed rel      规定当前文档与被链接文档之间的关系
 * @property mixed sizes    规定被链接资源的尺寸。仅适用于 rel="icon"
 * @property mixed type     规定被链接文档的 MIME 类型
 */
class Link extends Tag implements TopElement
{

    protected $_tagName = 'link';

    /**
     * @var bool 是否成对标签
     */
    protected $_tagClosed = false;

    public function init()
    {
        if ( ! $this->rel)
        {
            $this->rel = 'stylesheet';
        }
        if ( ! $this->type)
        {
            $this->type = 'text/css';
        }
    }

    /**
     * @return null|string|array
     */
    public function getHref()
    {
        return $this->getAttribute('href');
    }

    /**
     * @param $href
     */
    public function setHref($href)
    {
        $this->setAttribute('href', $href);
    }

    /**
     * @return null|string|array
     */
    public function getHrefLang()
    {
        return $this->getAttribute('hrefLang');
    }

    /**
     * @param $hrefLang
     */
    public function setHrefLang($hrefLang)
    {
        $this->setAttribute('hrefLang', $hrefLang);
    }

    /**
     * @return null|string|array
     */
    public function getMedia()
    {
        return $this->getAttribute('media');
    }

    /**
     * @param $media
     */
    public function setMedia($media)
    {
        $this->setAttribute('media', $media);
    }

    /**
     * @return null|string|array
     */
    public function getRel()
    {
        return $this->getAttribute('rel');
    }

    /**
     * @param $rel
     */
    public function setRel($rel)
    {
        $this->setAttribute('rel', $rel);
    }

    /**
     * @return null|string|array
     */
    public function getSizes()
    {
        return $this->getAttribute('sizes');
    }

    /**
     * @param $sizes
     */
    public function setSizes($sizes)
    {
        $this->setAttribute('sizes', $sizes);
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

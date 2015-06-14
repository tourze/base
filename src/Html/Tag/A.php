<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * A标签，A标签一般拥有以下属性：
 *
 * @property mixed download 规定被下载的超链接目标
 * @property mixed href     规定链接指向的页面的 URL
 * @property mixed hrefLang 规定被链接文档的语言
 * @property mixed media    规定被链接文档是为何种媒介/设备优化的
 * @property mixed rel      规定当前文档与被链接文档之间的关系
 * @property mixed target   规定在何处打开链接文档
 * @property mixed type     规定被链接文档的的 MIME 类型
 */
class A extends Tag implements InlineElement
{

    protected $_tagName = 'a';

    /**
     * H5的新属性，规定被下载的超链接目标
     *
     * @return null|string|array
     */
    public function getDownload()
    {
        return $this->getAttribute('download');
    }

    /**
     * H5的新属性，规定被下载的超链接目标
     *
     * @param $download
     */
    public function setDownload($download)
    {
        $this->setAttribute('download', $download);
    }

    /**
     * 读取class属性
     *
     * @return null|string|array
     */
    public function getHref()
    {
        return $this->getAttribute('href');
    }

    /**
     * 设置class属性
     *
     * @param $href
     */
    public function setHref($href)
    {
        $this->setAttribute('href', $href);
    }

    /**
     * 规定被链接文档的语言
     *
     * @return null|string|array
     */
    public function getHrefLang()
    {
        return $this->getAttribute('hrefLang');
    }

    /**
     * 规定被链接文档的语言
     *
     * @param $hrefLang
     */
    public function setHrefLang($hrefLang)
    {
        $this->setAttribute('hrefLang', $hrefLang);
    }

    /**
     * 规定被链接文档是为何种媒介/设备优化的。
     *
     * @return null|string|array
     */
    public function getMedia()
    {
        return $this->getAttribute('media');
    }

    /**
     * 规定被链接文档是为何种媒介/设备优化的。
     *
     * @param $media
     */
    public function setMedia($media)
    {
        $this->setAttribute('media', $media);
    }

    /**
     * 规定当前文档与被链接文档之间的关系。
     *
     * @return null|string|array
     */
    public function getRel()
    {
        return $this->getAttribute('rel');
    }

    /**
     * 规定当前文档与被链接文档之间的关系。
     *
     * @param $rel
     */
    public function setRel($rel)
    {
        $this->setAttribute('rel', $rel);
    }

    /**
     * 规定在何处打开链接文档。
     *
     * @return null|string|array
     */
    public function getTarget()
    {
        return $this->getAttribute('target');
    }

    /**
     * 规定在何处打开链接文档。
     *
     * @param $target
     */
    public function setTarget($target)
    {
        $this->setAttribute('target', $target);
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

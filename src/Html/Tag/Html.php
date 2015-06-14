<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * 此元素可告知浏览器其自身是一个HTML文档
 *
 * @property mixed manifest  定义一个 URL，在这个URL上描述了文档的缓存信息
 * @property mixed xmlns     定义`XML namespace`属性
 */
class Html extends Tag implements TopElement
{

    protected $_tagName = 'html';

    /**
     * @return null|string|array
     */
    public function getManifest()
    {
        return $this->getAttribute('manifest');
    }

    /**
     * @param $manifest
     */
    public function setManifest($manifest)
    {
        $this->setAttribute('manifest', $manifest);
    }

    /**
     * @return null|string|array
     */
    public function getXmlns()
    {
        return $this->getAttribute('xmlns');
    }

    /**
     * @param $xmlns
     */
    public function setXmlns($xmlns)
    {
        $this->setAttribute('xmlns', $xmlns);
    }

}

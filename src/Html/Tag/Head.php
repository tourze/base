<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\TopElement;
use tourze\Html\Tag;

/**
 * <head> 标签用于定义文档的头部，它是所有头部元素的容器。<head> 中的元素可以引用脚本、指示浏览器在哪里找到样式表、提供元信息等等。
 * 文档的头部描述了文档的各种属性和信息，包括文档的标题、在 Web 中的位置以及和其他文档的关系等。绝大多数文档头部包含的数据都不会真正作为内容显示给读者。
 *
 * @property mixed profile   一个由空格分隔的URL列表，这些URL包含着有关页面的元数据信息
 */
class Head extends Tag implements TopElement
{

    protected $_tagName = 'head';

    /**
     * @return null|string|array
     */
    public function getProfile()
    {
        return $this->getAttribute('profile');
    }

    /**
     * @param $profile
     */
    public function setProfile($profile)
    {
        $this->setAttribute('profile', $profile);
    }

}

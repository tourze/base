<?php

namespace tourze\Html\Feature;

/**
 * 通用的标签属性
 *
 * @package tourze\Html\Feature
 */
trait Common
{

    /**
     * 读取指定属性值
     *
     * @param $name
     *
     * @return null|string|array
     */
    protected function getAttribute($name)
    {
    }

    /**
     * 设置属性值
     *
     * @param $name
     * @param $value
     */
    protected function setAttribute($name, $value)
    {
    }

    /**
     * 规定激活元素的快捷键。
     *
     * @return null|string|array
     */
    public function getAccessKey()
    {
        return $this->getAttribute('accessKey');
    }

    /**
     * 规定激活元素的快捷键。
     *
     * @param $accessKey
     */
    public function setAccessKey($accessKey)
    {
        $this->setAttribute('accessKey', $accessKey);
    }

    /**
     * 读取class属性
     *
     * @return null|string|array
     */
    public function getClass()
    {
        return $this->getAttribute('class');
    }

    /**
     * 设置class属性
     *
     * @param $class
     */
    public function setClass($class)
    {
        $this->setAttribute('class', $class);
    }

    /**
     * 规定元素内容是否可编辑。
     *
     * @return null|string|array
     */
    public function getContentEditable()
    {
        return $this->getAttribute('contentEditable');
    }

    /**
     * 规定元素内容是否可编辑。
     *
     * @param $contentEditable
     */
    public function setContentEditable($contentEditable)
    {
        $this->setAttribute('contentEditable', $contentEditable);
    }

    /**
     * 规定元素的上下文菜单。上下文菜单在用户点击元素时显示。
     *
     * @return null|string|array
     */
    public function getContextMenu()
    {
        return $this->getAttribute('contextMenu');
    }

    /**
     * 规定元素的上下文菜单。上下文菜单在用户点击元素时显示。
     *
     * @param $contextMenu
     */
    public function setContextMenu($contextMenu)
    {
        $this->setAttribute('contextMenu', $contextMenu);
    }

    /**
     * 规定元素中内容的文本方向。
     *
     * @return null|string|array
     */
    public function getDir()
    {
        return $this->getAttribute('dir');
    }

    /**
     * 规定元素中内容的文本方向。
     *
     * @param $dir
     */
    public function setDir($dir)
    {
        $this->setAttribute('dir', $dir);
    }

    /**
     * 规定元素是否可拖动。
     *
     * @return null|string|array
     */
    public function getDraggable()
    {
        return $this->getAttribute('draggable');
    }

    /**
     * 规定元素是否可拖动。
     *
     * @param $draggable
     */
    public function setDraggable($draggable)
    {
        $this->setAttribute('draggable', $draggable);
    }

    /**
     * 规定在拖动被拖动数据时是否进行复制、移动或链接。
     *
     * @return null|string|array
     */
    public function getDropZone()
    {
        return $this->getAttribute('dropZone');
    }

    /**
     * 规定在拖动被拖动数据时是否进行复制、移动或链接。
     *
     * @param $dropZone
     */
    public function setDropZone($dropZone)
    {
        $this->setAttribute('dropZone', $dropZone);
    }

    /**
     * 规定元素仍未或不再相关。
     *
     * @return null|string|array
     */
    public function getHidden()
    {
        return $this->getAttribute('hidden');
    }

    /**
     * 规定元素仍未或不再相关。
     *
     * @param $hidden
     */
    public function setHidden($hidden)
    {
        $this->setAttribute('hidden', $hidden);
    }

    /**
     * 读取id属性
     *
     * @return null|string|array
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * 设置id属性
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->setAttribute('id', $id);
    }

    /**
     * 规定元素内容的语言。
     *
     * @return null|string|array
     */
    public function getLang()
    {
        return $this->getAttribute('lang');
    }

    /**
     * 规定元素内容的语言。
     *
     * @param $lang
     */
    public function setLang($lang)
    {
        $this->setAttribute('lang', $lang);
    }

    /**
     * 规定是否对元素进行拼写和语法检查。
     *
     * @return null|string|array
     */
    public function getSpellCheck()
    {
        return $this->getAttribute('spellCheck');
    }

    /**
     * 规定是否对元素进行拼写和语法检查。
     *
     * @param $spellCheck
     */
    public function setSpellCheck($spellCheck)
    {
        $this->setAttribute('spellCheck', $spellCheck);
    }

    /**
     * 读取style属性
     *
     * @return null|string|array
     */
    public function getStyle()
    {
        return $this->getAttribute('style');
    }

    /**
     * 设置style属性
     *
     * @param $style
     */
    public function setStyle($style)
    {
        $this->setAttribute('style', $style);
    }

    /**
     * 规定元素的 tab 键次序。
     *
     * @return null|string|array
     */
    public function getTabIndex()
    {
        return $this->getAttribute('tabIndex');
    }

    /**
     * 规定元素的 tab 键次序。
     *
     * @param $tabIndex
     */
    public function setTabIndex($tabIndex)
    {
        $this->setAttribute('tabIndex', $tabIndex);
    }

    /**
     * 读取title属性
     *
     * @return null|string|array
     */
    public function getTitle()
    {
        return $this->getAttribute('title');
    }

    /**
     * 设置title属性
     *
     * @param $title
     */
    public function setTitle($title)
    {
        $this->setAttribute('title', $title);
    }

    /**
     * 规定是否应该翻译元素内容。
     *
     * @return null|string|array
     */
    public function getTranslate()
    {
        return $this->getAttribute('translate');
    }

    /**
     * 规定是否应该翻译元素内容。
     *
     * @param $translate
     */
    public function setTranslate($translate)
    {
        $this->setAttribute('translate', $translate);
    }
}

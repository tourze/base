<?php

namespace tourze\Html;

/**
 * HTML标签基础类
 *
 * @property string  body             内容
 * @property mixed   accessKey        规定激活元素的快捷键
 * @property mixed   class            规定元素的一个或多个类名（引用样式表中的类）。
 * @property mixed   contentEditable  规定元素内容是否可编辑。
 * @property mixed   contextMenu      规定元素的上下文菜单。上下文菜单在用户点击元素时显示。
 * @property mixed   dir              规定元素中内容的文本方向。
 * @property mixed   draggable        规定元素是否可拖动。
 * @property mixed   dropZone         规定在拖动被拖动数据时是否进行复制、移动或链接。
 * @property mixed   hidden           规定元素仍未或不再相关。
 * @property mixed   id               规定元素的唯一 id。
 * @property mixed   lang             规定元素内容的语言。
 * @property mixed   spellCheck       规定是否对元素进行拼写和语法检查。
 * @property mixed   style            规定元素的行内 CSS 样式。
 * @property mixed   tabIndex         规定元素的 tab 键次序。
 * @property mixed   title            规定有关元素的额外信息。
 * @property mixed   translate        规定是否应该翻译元素内容。
 * @package tourze\Html
 */
class Tag extends Html
{

    protected $_tagName = false;

    /**
     * @var string 内部内容
     */
    protected $_innerBody = '';

    /**
     * @var bool 是否成对标签
     */
    protected $_tagClosed = true;

    /**
     * @var  array  preferred order of attributes
     */
    public static $attributeOrder = [
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxLength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabIndex',
        'accessKey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled',
        'body',
    ];

    /**
     * Convert special characters to HTML entities. All untrusted content
     * should be passed through this method to prevent XSS injections.
     *     echo self::chars($username);
     *
     * @param   string  $value        string to convert
     * @param   boolean $doubleEncode encode existing entities
     *
     * @return  string
     */
    public static function chars($value, $doubleEncode = true)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'utf-8', $doubleEncode);
    }

    /**
     * @var array 当前标签的属性值
     */
    protected $_attributes = [];

    public function __construct($args = [], $body = '')
    {
        $requestArgs = func_get_args();
        if (count($requestArgs) > 2)
        {
            $args = array_shift($requestArgs);
            $body = implode('', $requestArgs);
        }

        parent::__construct($args);
        $this->_innerBody = $body;
    }

    public function getBody()
    {
        return $this->_innerBody;
    }

    public function setBody($body)
    {
        $this->_innerBody = $body;
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

    /**
     * 读取指定属性值
     *
     * @param $name
     *
     * @return null|string|array
     */
    protected function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    /**
     * 设置属性值
     *
     * @param $name
     * @param $value
     */
    protected function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     * 合并属性值
     *
     * @return string
     */
    protected function combineAttributes()
    {
        $attributes = $this->_attributes;

        // 对属性进行排序
        $sorted = [];
        foreach (self::$attributeOrder as $key)
        {
            if (isset($attributes[$key]))
            {
                $sorted[$key] = $attributes[$key];
            }
        }
        // 再合并
        $attributes = $sorted + $attributes;

        $compiled = '';
        foreach ($attributes as $key => $value)
        {
            if (null === $value)
            {
                // Skip attributes that have null values
                continue;
            }

            // Add the attribute key
            $compiled .= ' ' . strtolower($key);

            if ($value)
            {
                // Add the attribute value
                $compiled .= '="' . self::chars($value) . '"';
            }
        }

        return $compiled;
    }

    /**
     * 渲染标签
     *
     * @return string
     */
    protected function render()
    {
        if ( ! $this->_tagName)
        {
            return '';
        }

        $attribute = $this->combineAttributes();
        if ($this->_tagClosed)
        {
            return "<{$this->_tagName}{$attribute}>{$this->_innerBody}</{$this->_tagName}>";
        }
        else
        {
            return "<{$this->_tagName}{$attribute} />";
        }
    }

    public function __toString()
    {
        return $this->render();
    }

}

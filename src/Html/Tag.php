<?php

namespace tourze\Html;
use tourze\Html\Feature\Common;
use tourze\Html\Feature\EventHandlers;
use tourze\Html\Feature\InternetExplorerAttributes;
use tourze\Html\Feature\KeyboardMouseAttributes;
use tourze\Html\Feature\StandardEventAttributes;
use tourze\Html\Feature\StandardEventMethods;

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

    /**
     * @var string 当前标签名
     */
    protected $_tagName = '';

    /**
     * @var string 内部内容
     */
    protected $_innerBody = '';

    /**
     * @var bool 是否成对标签
     */
    protected $_tagClosed = true;

    /**
     * @var  array  默认标签的排序方式
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

    /**
     * 构造方法
     *
     * @param array  $args
     * @param string $body
     */
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

    /**
     * 获取当前标签的内嵌内容
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_innerBody;
    }

    /**
     * 设置当前标签的内嵌内容
     *
     * @param $body
     */
    public function setBody($body)
    {
        $this->_innerBody = $body;
    }

    use
        Common,
        EventHandlers,
        InternetExplorerAttributes,
        KeyboardMouseAttributes,
        StandardEventAttributes {

    };

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

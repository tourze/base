<?php

namespace tourze\Html;

use tourze\Html\Feature\Base;
use tourze\Html\Feature\Common;
use tourze\Html\Feature\EventHandlers;
use tourze\Html\Feature\InternetExplorerAttributes;
use tourze\Html\Feature\KeyboardMouseAttributes;
use tourze\Html\Feature\StandardEventAttributes;

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
 * @method   mixed   combineAttributes()
 * @method   void    setAttribute($name, $value)
 * @method   null|string|array getAttribute($name)
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

        foreach ($args as $k => $v)
        {
            if (strpos($k, 'data-'))
            {
                $this->setAttribute($k, $v);
                unset($args[$k]);
            }
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
        Base,
        Common,
        EventHandlers,
        InternetExplorerAttributes,
        KeyboardMouseAttributes,
        StandardEventAttributes
    {

        Base::getAttribute insteadof Common;
        Base::getAttribute insteadof EventHandlers;
        Base::getAttribute insteadof InternetExplorerAttributes;
        Base::getAttribute insteadof KeyboardMouseAttributes;
        Base::getAttribute insteadof StandardEventAttributes;

        Base::setAttribute insteadof Common;
        Base::setAttribute insteadof EventHandlers;
        Base::setAttribute insteadof InternetExplorerAttributes;
        Base::setAttribute insteadof KeyboardMouseAttributes;
        Base::setAttribute insteadof StandardEventAttributes;

        //Base::combineAttributes as combineAttributes;
        //Base::getAttribute as getAttribute;
        //Base::setAttribute as setAttribute;
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

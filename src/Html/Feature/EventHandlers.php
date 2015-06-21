<?php

namespace tourze\Html\Feature;

/**
 * 事件句柄 (Event Handlers)
 *
 * HTML 4.0 的新特性之一是能够使 HTML 事件触发浏览器中的行为，比如当用户点击某个 HTML 元素时启动一段 JavaScript。
 *
 * @package tourze\Html\Feature
 */
trait EventHandlers
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
     * 当用户点击某个对象时调用的事件句柄
     *
     * @return null|string|array
     */
    public function getOnClick()
    {
        return $this->getAttribute('onClick');
    }

    /**
     * 当用户点击某个对象时调用的事件句柄
     *
     * @param $onClick
     */
    public function setOnClick($onClick)
    {
        $this->setAttribute('onClick', $onClick);
    }
}

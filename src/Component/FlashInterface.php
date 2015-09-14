<?php

namespace tourze\Base\Component;

use tourze\Base\ComponentInterface;

/**
 * Interface FlashInterface
 *
 * @package tourze\Base\Component
 */
interface FlashInterface extends ComponentInterface
{
    /**
     * 设置flash数据
     *
     * @param string $key
     * @param mixed  $value
     */
    public function flash($key, $value);

    /**
     * 获取完整的flash数据
     *
     * @return array
     */
    public function data();

    /**
     * 直接设置当前请求的flash数据
     *
     * @param  string $key
     * @param  string $value
     */
    public function now($key, $value);

    /**
     * 设置flash数据，会在下次请求时展示
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);

    /**
     * 保留住当前数据
     */
    public function keep();

    /**
     * 保存Flash数据
     */
    public function save();

    /**
     * 初始化和加载消息列表，默认会加载上次请求保存的消息
     */
    public function loadMessages();

    /**
     * 返回请求的消息
     *
     * @return array
     */
    public function getMessages();
}

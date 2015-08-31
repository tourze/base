<?php

namespace tourze\Base\Component;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use tourze\Base\Base;
use tourze\Base\Component;

/**
 * Flash组件
 *
 * @package tourze\Base\Component
 */
class Flash extends Component implements ArrayAccess, IteratorAggregate, Countable
{

    /**
     * @var array
     */
    public $settings = [
        'key' => 'tourze.flash',
    ];

    /**
     * @var array
     */
    public $messages = [
        'prev' => [],
        'next' => [],
        'now'  => [],
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // 加载会话消息
        $this->loadMessages();
    }

    /**
     * 设置flash数据
     *
     * @param string $key
     * @param mixed  $value
     */
    public function flash($key, $value)
    {
        $this->set($key, $value);
        $this->save();
    }

    /**
     * 获取完整的flash数据
     *
     * @return array
     */
    public function data()
    {
        Base::getLog()->info(__METHOD__ . ' fetch all flash data');
        $result = $this->getMessages();
        $this->save();

        return $result;
    }

    /**
     * 直接设置当前请求的flash数据
     *
     * @param  string $key
     * @param  string $value
     */
    public function now($key, $value)
    {
        Base::getLog()->info(__METHOD__ . ' set flash at current request', [
            'key'   => $key,
            'value' => $value,
        ]);
        $this->messages['now'][(string) $key] = $value;
    }

    /**
     * 设置flash数据，会在下次请求时展示
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        Base::getLog()->info(__METHOD__ . ' set flash', [
            'key'   => $key,
            'value' => $value,
        ]);
        $this->messages['next'][(string) $key] = $value;
    }

    /**
     * 保留住当前数据
     */
    public function keep()
    {
        Base::getLog()->info(__METHOD__ . ' keep flash');
        foreach ($this->messages['prev'] as $key => $val)
        {
            $this->messages['next'][$key] = $val;
        }
    }

    /**
     * 保存Flash数据
     */
    public function save()
    {
        Base::getLog()->info(__METHOD__ . ' save flash');
        Base::getSession()->set($this->settings['key'], $this->messages['next']);
    }

    /**
     * 初始化和加载消息列表，默认会加载上次请求保存的消息
     */
    public function loadMessages()
    {
        Base::getLog()->info(__METHOD__ . ' load flash messages');
        if ($value = Base::getSession()->get($this->settings['key']))
        {
            $this->messages['prev'] = $value;
        }
    }

    /**
     * 返回请求的消息
     *
     * @return array
     */
    public function getMessages()
    {
        Base::getLog()->info(__METHOD__ . ' get flash messages');
        return array_merge($this->messages['prev'], $this->messages['now']);
    }

    /**
     * 检查指定key是否存在
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $messages = $this->getMessages();
        return isset($messages[$offset]);
    }

    /**
     * 读取指定key
     *
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        $messages = $this->getMessages();
        return isset($messages[$offset]) ? $messages[$offset] : null;
    }

    /**
     * 设置指定key
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->now($offset, $value);
    }

    /**
     * 删除指定key
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->messages['prev'][$offset], $this->messages['now'][$offset]);
    }

    /**
     * 获取一个迭代器
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $messages = $this->getMessages();
        return new ArrayIterator($messages);
    }

    /**
     * 返回消息总条数
     */
    public function count()
    {
        return count($this->getMessages());
    }
}

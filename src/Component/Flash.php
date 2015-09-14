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
class Flash extends Component implements ArrayAccess, IteratorAggregate, Countable, FlashInterface
{

    /**
     * @inheritdoc
     */
    public $persistence = false;

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
     * {@inheritdoc}
     */
    public function flash($key, $value)
    {
        $this->set($key, $value);
        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function data()
    {
        Base::getLog()->debug(__METHOD__ . ' fetch all flash data');
        $result = $this->getMessages();
        $this->save();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function now($key, $value)
    {
        Base::getLog()->debug(__METHOD__ . ' set flash at current request', [
            'key'   => $key,
            'value' => $value,
        ]);
        $this->messages['now'][(string) $key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        Base::getLog()->debug(__METHOD__ . ' set flash', [
            'key'   => $key,
            'value' => $value,
        ]);
        $this->messages['next'][(string) $key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function keep()
    {
        Base::getLog()->debug(__METHOD__ . ' keep flash');
        foreach ($this->messages['prev'] as $key => $val)
        {
            $this->messages['next'][$key] = $val;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        Base::getLog()->debug(__METHOD__ . ' save flash');
        Base::getSession()->set($this->settings['key'], $this->messages['next']);
    }

    /**
     * {@inheritdoc}
     */
    public function loadMessages()
    {
        Base::getLog()->debug(__METHOD__ . ' load flash messages from session');
        if ($value = Base::getSession()->get($this->settings['key']))
        {
            $this->messages['prev'] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        Base::getLog()->debug(__METHOD__ . ' get flash messages');
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

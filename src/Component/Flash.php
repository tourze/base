<?php

namespace tourze\Base\Component;

use ArrayAccess;
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
        'prev' => [], //flash messages from prev request (loaded when middleware called)
        'next' => [], //flash messages for next request
        'now'  => [] //flash messages for current request
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
     * Set flash message for subsequent request
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function flash($key, $value)
    {
        $this->set($key, $value);
        $this->save();
    }

    /**
     * Get all flash messages
     */
    public function data()
    {
        $result = $this->getMessages();
        $this->save();

        return $result;
    }

    /**
     * Now
     *
     * Specify a flash message for a given key to be shown for the current request
     *
     * @param  string $key
     * @param  string $value
     */
    public function now($key, $value)
    {
        $this->messages['now'][(string) $key] = $value;
    }

    /**
     * Set
     *
     * Specify a flash message for a given key to be shown for the next request
     *
     * @param  string $key
     * @param  string $value
     */
    public function set($key, $value)
    {
        $this->messages['next'][(string) $key] = $value;
    }

    /**
     * Keep
     *
     * Retain flash messages from the previous request for the next request
     */
    public function keep()
    {
        foreach ($this->messages['prev'] as $key => $val)
        {
            $this->messages['next'][$key] = $val;
        }
    }

    /**
     * Save
     */
    public function save()
    {
        Base::getSession()->set($this->settings['key'], $this->messages['next']);
    }

    /**
     * Load messages from previous request if available
     */
    public function loadMessages()
    {
        if ($value = Session::instance()->get($this->settings['key']))
        {
            $this->messages['prev'] = $value;
        }
    }

    /**
     * Return array of flash messages to be shown for the current request
     *
     * @return array
     */
    public function getMessages()
    {
        return array_merge($this->messages['prev'], $this->messages['now']);
    }

    /**
     * Array Access: Offset Exists
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
     * Array Access: Offset Get
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
     * Array Access: Offset Set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->now($offset, $value);
    }

    /**
     * Array Access: Offset Unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->messages['prev'][$offset], $this->messages['now'][$offset]);
    }

    /**
     * Iterator Aggregate: Get Iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $messages = $this->getMessages();

        return new \ArrayIterator($messages);
    }

    /**
     * Countable: Count
     */
    public function count()
    {
        return count($this->getMessages());
    }
}

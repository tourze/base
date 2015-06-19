<?php

namespace tourze\Base\Helper;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use tourze\Base\Object;
use tourze\Session\Session;

/**
 * Flash
 *
 * This is middleware for a Slim application that enables
 * Flash messaging between HTTP requests. This allows you
 * set Flash messages for the current request, for the next request,
 * or to retain messages from the previous request through to
 * the next request.
 *
 * @package    Slim
 * @author     Josh Lockhart
 * @since      1.6.0
 */
class Flash extends Object implements ArrayAccess, IteratorAggregate, Countable
{

    /**
     * Set flash message for subsequent request
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public static function flash($key, $value)
    {
        self::instance()->set($key, $value);
    }

    /**
     * Set flash message for current request
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public static function flashNow($key, $value)
    {
        self::instance()->now($key, $value);
    }

    /**
     * Keep flash messages from previous request for subsequent request
     */
    public static function flashKeep()
    {
        self::instance()->keep();
    }

    /**
     * Get all flash messages
     */
    public static function flashData()
    {
        return self::instance()->getMessages();
    }

    /**
     * @var array
     */
    protected $settings = [
        'key' => 'tourze.flash',
    ];

    /**
     * @var array
     */
    protected $messages;

    /**
     * Constructor
     *
     * @param  array $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->messages = [
            'prev' => [], //flash messages from prev request (loaded when middleware called)
            'next' => [], //flash messages for next request
            'now'  => [] //flash messages for current request
        ];

        // 加载会话消息
        $this->loadMessages();
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
        Session::instance()->set($this->settings['key'], $this->messages['next']);
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

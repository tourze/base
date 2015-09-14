<?php

namespace tourze\Base\Component;

use PHPUnit_Framework_TestCase;
use tourze\Base\Base;

/**
 * 缓存组件测试用例
 *
 * @package tourze\Base\Component
 */
class CacheTest extends PHPUnit_Framework_TestCase
{

    public function testCache()
    {
        $key = uniqid();
        $value = time();
        Base::getCache()->set($key, $value);

        $this->assertEquals($value, Base::getCache()->get($key));
    }

}

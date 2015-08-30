<?php

namespace tourze\Base;

/**
 * Base测试用例
 *
 * @package tourze\Base
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{

    public function providerLoad()
    {
        return [
            [__DIR__ . '/../data/test_array.php', [
                'key1' => 'TEST',
                'key2' => '5566'
            ]],
            [__DIR__ . '/../data/test_array1.php', [
                'ey1' => 0,
                'key2' => null
            ]],
        ];
    }

    /**
     * 检测load方法的结果是否正确
     *
     * @dataProvider providerLoad
     * @param string $path
     * @param mixed  $result
     */
    public function testLoad($path, $result)
    {
        $this->assertEquals($result, Base::load($path));
    }

    public function providerCache()
    {
        return [
            ['foo', 'bar~', null, null],
            ['foo1', 'bar1', 2, 1],
            ['foo2', ['test3', 1234], 2, 1],
        ];
    }

    /**
     * 检测cache方法是否正确
     *
     * @dataProvider providerCache
     * @param string $name
     * @param mixed  $data
     * @param int    $expired
     * @param int    $sleep sleep参数，用于调试过期时间是否正确
     */
    public function testCache($name, $data, $expired, $sleep = 0)
    {
        // 写，然后读
        Base::cache($name, $data, $expired);
        if ($sleep)
        {
            sleep($sleep);
        }
        $result = Base::cache($name);

        $this->assertEquals($data, $result);
    }
}

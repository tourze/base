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
}

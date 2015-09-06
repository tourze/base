<?php

namespace tourze\Base;

/**
 * Base测试用例
 *
 * @package tourze\Base
 */
class SecurityTest extends \PHPUnit_Framework_TestCase
{

    public function providerSanitize()
    {
        return [
            ['1', '1'],
            ["\r\n\r\n", "\n\n"],
            ["Is your name O'reilly?", "Is your name O'reilly?"]
        ];
    }

    /**
     * 检测过滤是否正确
     *
     * @dataProvider providerSanitize
     * @param mixed $input
     * @param mixed $output
     */
    public function testSanitize($input, $output)
    {
        $this->assertEquals($output, Security::sanitize($input));
    }
}

<?php

namespace tourze\Base;

use PHPUnit_Framework_TestCase;

/**
 * 配置加载器测试用例
 *
 * @package tourze\Base
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        Config::addPath(__DIR__ . '/data/config/');
    }

    /**
     * @return array
     */
    public function dataAddPath()
    {
        return [
            [uniqid()],
            [uniqid()],
        ];
    }

    /**
     * 测试addPath方法是否OK
     *
     * @dataProvider dataAddPath
     * @param string $path
     */
    public function testAddPath($path)
    {
        Config::addPath($path);
        $currentPaths = Config::getPaths();
        $this->assertTrue(isset($currentPaths[$path]));
    }

    /**
     * @return array
     */
    public function dataLoad()
    {
        return [
            ['test', 'test1', 'val1'],
            ['test', 'test2.test3', 'val3'],
        ];
    }

    /**
     * 测试配置加载功能
     *
     * @dataProvider dataLoad
     * @param mixed $file
     * @param mixed $name
     * @param mixed $expected
     */
    public function testLoad($file, $name, $expected)
    {
        $this->assertEquals($expected, Config::load($file)->get($name));
    }
}

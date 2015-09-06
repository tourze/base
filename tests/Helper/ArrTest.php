<?php

namespace tourze\Base\Helper;

use ArrayObject;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Class ArrTest
 *
 * @package tourze\Base\Helper
 */
class ArrTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return array
     */
    public function dataIsAssoc()
    {
        return [
            [['john', 'lily'], false],
            [new ArrayObject, false],
            [new stdClass, false],
            [['username' => 'john'], true],
            [[0 => 'test', 'username' => 'agent', 1 => 'super'], true], // 注意这种情况
        ];
    }

    /**
     * 检测[Arr::isAssoc]功能是否正常
     *
     * @dataProvider dataIsAssoc
     * @param mixed $input
     * @param bool  $result
     */
    public function testIsAssoc($input, $result)
    {
        $this->assertEquals($result, Arr::isAssoc($input));
    }

    /**
     * @return array
     */
    public function dataIsArray()
    {
        return [
            [[], true],
            [[1, 2], true],
            [['username' => 'john'], true],
            [['username' => 'john', 1 => 'mary'], true],
            [new ArrayObject, true],
            [new stdClass, false],
            [new Arr, false],
        ];
    }

    /**
     * @dataProvider dataIsArray
     * @param mixed $input
     * @param bool  $result
     */
    public function testIsArray($input, $result)
    {
        $this->assertEquals($result, Arr::isArray($input));
    }

    /**
     * 检测[Arr::path]是否工作正常
     */
    public function testPath()
    {
        $arr = [
            'component' => [
                'http'    => [
                    'driver' => 'HttpDriver',
                    'args'   => [],
                ],
                'session' => [
                    'driver' => 'SessionDriver',
                    'args'   => [],
                ],
            ],
        ];

        // 最简单的
        $this->assertEquals([
            'driver' => 'HttpDriver',
            'args'   => [],
        ], Arr::path($arr, 'component.http'));
        $this->assertEquals([
            'driver' => 'HttpDriver',
            'args'   => [],
        ], Arr::path($arr, ['component', 'http']));

        // 使用通配符试试
        $this->assertEquals([
            'HttpDriver',
            'SessionDriver',
        ], Arr::path($arr, 'component.*.driver'));
        $this->assertEquals([
            'HttpDriver',
            'SessionDriver',
        ], Arr::path($arr, ['component', '*', 'driver']));

        // 试试默认返回值
        $this->assertEquals('DEFAULT', Arr::path($arr, 'fake.fake', 'DEFAULT'));
        $this->assertEquals('DEFAULT', Arr::path($arr, ['fake', 'fake'], 'DEFAULT'));
    }

    /**
     * 检验[Arr::setPath]功能
     */
    public function testSetPath()
    {
        $arr = [];
        Arr::setPath($arr, 'test1.test1', 'TEST1');
        $this->assertEquals('TEST1', Arr::path($arr, 'test1.test1'));

        $arr = [];
        Arr::setPath($arr, ['test2', 'test2'], 'TEST2');
        $this->assertEquals('TEST2', Arr::path($arr, ['test2', 'test2']));
    }

    /**
     * @return array
     */
    public function dataRange()
    {
        return [
            [1, 5, [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]],
            [5, 20, [5 => 5, 10 => 10, 15 => 15, 20 => 20]],
            ['2', '10', [2 => 2, 4 => 4, 6 => 6, 8 => 8, 10 => 10]],
        ];
    }

    /**
     * 校验[Arr::range]方法
     *
     * @dataProvider dataRange
     * @param mixed $step
     * @param mixed $max
     * @param mixed $expect
     */
    public function testRange($step, $max, $expect)
    {
        $this->assertEquals($expect, Arr::range($step, $max));
    }

    /**
     * @return array
     */
    public function dataGet()
    {
        return [
            [[0 => 'INDEX1', 1 => 'INDEX2'], 0, 'INDEX1'],
            [[0 => 'INDEX1', 1 => 'INDEX2'], 1, 'INDEX2'],
            [[0 => 'INDEX1', 1 => 'INDEX2'], '0', 'INDEX1'],
            [[0 => 'INDEX1', 1 => 'INDEX2'], '1', 'INDEX2'],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], 'foo1', 'default', 'default'],
        ];
    }

    /**
     * 测试[Arr::get]的功能
     *
     * @dataProvider dataGet
     * @param array $arr
     * @param mixed $key
     * @param mixed $expect
     * @param mixed $default
     */
    public function testGet($arr, $key, $expect, $default = null)
    {
        $this->assertEquals($expect, Arr::get($arr, $key, $default));
    }

    /**
     * @return array
     */
    public function dataHas()
    {
        return [
            [['foo' => 'bar'], 'foo', true],
            [['foo' => 'bar'], 'bar', false],
            [['foo', 'bar'], 0, true],
            [['foo', 'bar'], 1, true],
            [['foo', 'bar'], 2, false],
        ];
    }

    /**
     * 测试[Arr::has]的功能
     *
     * @dataProvider dataHas
     * @param array $arr
     * @param mixed $key
     * @param mixed $expect
     */
    public function testHas($arr, $key, $expect)
    {
        $this->assertEquals($expect, Arr::has($arr, $key));
    }

    /**
     * @return array
     */
    public function dataExtract()
    {
        return [
            [
                ['key1' => 'val1', 'key2' => ['key3' => 'val3']],
                'key1',
                ['key1' => 'val1'],
            ],
            [
                ['key1' => 'val1', 'key2' => ['key3' => 'val3'], 'key3' => 'val3'],
                ['key1', 'key3'],
                ['key1' => 'val1', 'key3' => 'val3'],
            ],
            [
                ['key1' => 'val1', 'key2' => ['key3' => 'val3'], 'key3' => 'val3'],
                ['key1' => 'default1', 'key3' => 'default3', 'key_not_found' => 'key_not_found_default'],
                ['key1' => 'val1', 'key3' => 'val3', 'key_not_found' => 'key_not_found_default'],
            ],
        ];
    }

    /**
     * 测试[Arr::extract]的功能
     *
     * @dataProvider dataExtract
     * @param mixed $arr
     * @param mixed $paths
     * @param mixed $expected
     * @param mixed $default
     */
    public function testExtract($arr, $paths, $expected, $default = null)
    {
        $this->assertEquals($expected, Arr::extract($arr, $paths, $default));
    }

    /**
     * @return array
     */
    public function dataUnshift()
    {
        return [
            ['i1', 'v1'],
            [0, 'v1'],
            [1, 'v1'],
        ];
    }

    /**
     * 测试[Arr::unshift]的功能
     *
     * @dataProvider dataUnshift
     * @param mixed $key
     * @param mixed $val
     */
    public function testUnshift($key, $val)
    {
        $arr = [
            uniqid(time()) => uniqid(),
            uniqid() => uniqid(),
        ];

        Arr::unshift($arr, $key, $val);
        $this->assertEquals(true, Arr::get($arr, $key) === $val && array_search($key, $arr) == 0);
    }

    /**
     * @return array
     */
    public function dataMap()
    {
        return [
            [[' TRIM '], 'trim', ['TRIM']],
            [[' TRIM '], ['strtolower', 'trim'], ['trim']],
        ];
    }

    /**
     * 测试[Arr::clean]方法
     *
     * @dataProvider dataMap
     * @param mixed $arr
     * @param mixed $callback
     * @param mixed $expected
     */
    public function testMap($arr, $callback, $expected)
    {
        $this->assertEquals($expected, Arr::map($arr, $callback));
    }

    /**
     * @return array
     */
    public function dataMerge()
    {
        return [
            [
                [1, 2],
                [3],
                [1, 2, 3],
            ],
            [
                ['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']],
                ['name' => 'mary', 'children' => ['jane']],
                ['name' => 'mary', 'children' => ['fred', 'paul', 'sally', 'jane']]
            ],
            [
                ['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']],
                ['name' => 'mary', 'children' => ['jane', 'lily']],
                ['name' => 'mary', 'children' => ['fred', 'paul', 'sally', 'jane', 'lily']]
            ],
            [
                ['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']],
                ['name' => 'mary', 'children' => false],
                ['name' => 'mary', 'children' => false]
            ],
        ];
    }

    /**
     * 检验[Arr::merge]的功能
     *
     * @dataProvider dataMerge
     * @param mixed $arr1
     * @param mixed $arr2
     * @param mixed $expected
     */
    public function testMerge($arr1, $arr2, $expected)
    {
        $this->assertEquals($expected, Arr::merge($arr1, $arr2));
    }

    /**
     * @return array
     */
    public function dataFlatten()
    {
        return [
            [
                ['set' => ['one' => 'something'], 'two' => 'other'],
                ['one' => 'something', 'two' => 'other']
            ]
        ];
    }

    /**
     * 测试[Arr::flatten]
     *
     * @dataProvider dataFlatten
     * @param mixed $arr
     * @param mixed $expected
     */
    public function testFlatten($arr, $expected)
    {
        $this->assertEquals($expected, Arr::flatten($arr));
    }
}

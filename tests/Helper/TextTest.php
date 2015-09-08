<?php

namespace tourze\Base\Helper;

use PHPUnit_Framework_TestCase;

class TextTest extends PHPUnit_Framework_TestCase
{

    public function dataLimitWords()
    {
        return [
            ['1 2 3 4', 2, null, '1 2…'],
            ['1 2 3 4', 2, '...', '1 2...'],
            ['Hello, I am teddy', 3, null, 'Hello, I am…'],
            ['Hello, I am teddy', 3, '...', 'Hello, I am...'],
        ];
    }

    /**
     * 测试[Text::limitWords]
     *
     * @dataProvider dataLimitWords
     * @param string $word
     * @param int    $limit
     * @param mixed  $endChars
     * @param mixed  $expected
     */
    public function testLimitWords($word, $limit, $endChars, $expected)
    {
        $this->assertEquals($expected, Text::limitWords($word, $limit, $endChars));
    }

    /**
     * @return array
     */
    public function dataLimitChars()
    {
        return [
            ['1 2 3 4', 2, null, '1…'],
            ['1 2 3 4', 3, null, '1 2…'],
        ];
    }

    /**
     * 测试[Text::limitChars]
     *
     * @dataProvider dataLimitChars
     * @param      $str
     * @param int  $limit
     * @param null $endChar
     * @param      $expected
     */
    public function testLimitChars($str, $limit = 100, $endChar = null, $expected)
    {
        $this->assertEquals($expected, Text::limitChars($str, $limit, $endChar));
    }

    /**
     * @return array
     */
    public function dataLength()
    {
        return [
            ['abc', 3],
            ['hello', 5],
        ];
    }

    /**
     * 测试[Text::length]
     *
     * @dataProvider dataLength
     * @param mixed $str
     * @param mixed $expected
     */
    public function testLength($str, $expected)
    {
        $this->assertEquals($expected, Text::length($str));
    }

    /**
     * 测试[Text::alternate]
     */
    public function testAlternate()
    {
        $str = Text::alternate('one', 'two');
        $this->assertEquals('one', $str);

        $str = Text::alternate('one', 'two');
        $this->assertEquals('two', $str);

        $str = Text::alternate('one', 'two');
        $this->assertEquals('one', $str);
    }

    /**
     * 测试[Text::random]
     */
    public function testRandom()
    {
        $str = Text::random(10);
        //echo ' ' . $str . ' ';
        $this->assertEquals(10, Text::length($str));

        $str = Text::random(3, 'q');
        //echo ' ' . $str . ' ';
        $this->assertEquals('qqq', $str);
    }

    /**
     * @return array
     */
    public function dataReduceSlashes()
    {
        return [
            ['foo//bar/baz', 'foo/bar/baz'],
            ['foo//bar//baz', 'foo/bar/baz'],
        ];
    }

    /**
     * 测试[Text::reduceSlashes]
     *
     * @dataProvider dataReduceSlashes
     * @param mixed $str
     * @param mixed $expected
     */
    public function testReduceSlashes($str, $expected)
    {
        $this->assertEquals($expected, Text::reduceSlashes($str));
    }

    /**
     * @return array
     */
    public function dataSimilar()
    {
        return [
            ['hello', 'hi', 'h'],
            ['mobile', 'more', 'mo'],
            ['active', 'activity', 'activ'],
        ];
    }

    /**
     * 测试[Text::censor]
     *
     * @dataProvider dataSimilar
     * @param $input1
     * @param $input2
     * @param $expected
     */
    public function testSimilar($input1, $input2, $expected)
    {
        $this->assertEquals($expected, Text::similar($input1, $input2));
    }

    /**
     * @return array
     */
    public function dataNumber()
    {
        return [
            [1024, 'one thousand and twenty-four'],
            [5000632, 'five million, six hundred and thirty-two'],
        ];
    }

    /**
     * 测试[Text::number]
     *
     * @dataProvider dataNumber
     * @param $input
     * @param $expected
     */
    public function testNumber($input, $expected)
    {
        $this->assertEquals($expected, Text::number($input));
    }

    /**
     * @return array
     */
    public function dataCamelize()
    {
        return [
            ['mother cat', 'motherCat'],
            ['kittens in bed', 'kittensInBed'],
        ];
    }

    /**
     * 测试[Text::camelize]
     *
     * @dataProvider dataCamelize
     * @param $input
     * @param $expected
     */
    public function testCamelize($input, $expected)
    {
        $this->assertEquals($expected, Text::camelize($input));
    }

    /**
     * @return array
     */
    public function dataDecamelize()
    {
        return [
            ['houseCat', 'house cat'],
            ['kingAllyCat', 'king ally cat'],
        ];
    }

    /**
     * 测试[Text::decamelize]
     *
     * @dataProvider dataDecamelize
     * @param $input
     * @param $expected
     */
    public function testDecamelize($input, $expected)
    {
        $this->assertEquals($expected, Text::decamelize($input));
    }

    /**
     * @return array
     */
    public function dataUnderscore()
    {
        return [
            ['five cats', 'five_cats'],
        ];
    }

    /**
     * 测试[Text::underscore]
     *
     * @dataProvider dataUnderscore
     * @param $input
     * @param $expected
     */
    public function testUnderscore($input, $expected)
    {
        $this->assertEquals($expected, Text::underscore($input));
    }

    /**
     * @return array
     */
    public function dataHumanize()
    {
        return [
            ['kittens-are-cats', 'kittens are cats'],
            ['this-is-a-title', 'this is a title'],
        ];
    }

    /**
     * 测试[Text::humanize]
     *
     * @dataProvider dataHumanize
     * @param $input
     * @param $expected
     */
    public function testHumanize($input, $expected)
    {
        $this->assertEquals($expected, Text::humanize($input));
    }

    /**
     * @return array
     */
    public function dataStartWith()
    {
        return [
            ['string', 's', true],
            ['hello world', 'he', true],
            ['hello world', 'world', false],
        ];
    }

    /**
     * 测试[Text::startWith]
     *
     * @dataProvider dataStartWith
     * @param $haystack
     * @param $needles
     * @param $expected
     */
    public function testStartWith($haystack, $needles, $expected)
    {
        $this->assertEquals($expected, Text::startWith($haystack, $needles));
    }

    /**
     * @return array
     */
    public function dataEndWith()
    {
        return [
            ['string', 'g', true],
            ['hello world', 'world', true],
            ['hello world', 'hello', false],
        ];
    }

    /**
     * 测试[Text::endWith]
     *
     * @dataProvider dataEndWith
     * @param $haystack
     * @param $needles
     * @param $expected
     */
    public function testEndWith($haystack, $needles, $expected)
    {
        $this->assertEquals($expected, Text::endWith($haystack, $needles));
    }

    /**
     * @return array
     */
    public function dataContains()
    {
        return [
            ['i am kitty', 'am', true],
            ['i am kitty', 'i', true],
            ['i am kitty', 'kitty', true],
            ['i am kitty', 'I', false],
            ['hello world', 'fake', false],
        ];
    }

    /**
     * 测试[Text::contains]
     *
     * @dataProvider dataContains
     * @param $haystack
     * @param $needles
     * @param $expected
     */
    public function testContains($haystack, $needles, $expected)
    {
        $this->assertEquals($expected, Text::contains($haystack, $needles));
    }

    /**
     * @return array
     */
    public function dataParams()
    {
        return [
            ['depth=2,something=test', ['depth' => 2, 'something' => 'test']],
        ];
    }

    /**
     * 测试[Text::params]
     *
     * @dataProvider dataParams
     * @param mixed $input
     * @param mixed $expected
     */
    public function testParams($input, $expected)
    {
        $this->assertEquals($expected, Text::params($input));
    }
}

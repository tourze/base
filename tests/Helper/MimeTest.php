<?php

namespace tourze\Base\Helper;

use PHPUnit_Framework_TestCase;

/**
 * 测试MIME助手类
 *
 * @package tourze\Base\Helper
 */
class MimeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return array
     */
    public function dateGetExtensionsFromMime()
    {
        return [
            ['video/3gpp', false, '3gp'],
            ['image/svg+xml', false, 'svg'],
            ['FAKE_MIME', false, ''],
        ];
    }

    /**
     * 检测[Mime::getExtensionsFromMime]
     *
     * @dataProvider dateGetExtensionsFromMime
     * @param string $mime
     * @param bool   $all
     * @param string $expected
     */
    public function testGetExtensionsFromMime($mime, $all, $expected)
    {
        $this->assertEquals($expected, Mime::getExtensionsFromMime($mime, $all));
    }

    /**
     * @return array
     */
    public function dataGetMimeFromExtension()
    {
        return [
            ['vsf', 'application/vnd.vsf'],
            ['zip', 'application/zip'],
            ['ttl', 'text/turtle'],
        ];
    }

    /**
     * 检测[Mime::getMimeFromExtension]
     *
     * @dataProvider dataGetMimeFromExtension
     * @param string $ext
     * @param string $expected
     */
    public function testGetMimeFromExtension($ext, $expected)
    {
        $this->assertEquals($expected, Mime::getMimeFromExtension($ext));
    }
}

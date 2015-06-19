<?php

namespace tourze\Base\Helper;

use Symfony\Component\Filesystem\Filesystem;

/**
 * 基于symfony/filesystem来做一个文件的助手类
 */
class File
{

    /**
     * @var Filesystem
     */
    protected static $_fileSystem = null;

    /**
     * @return Filesystem
     */
    public static function ensureFileSystem()
    {
        if (self::$_fileSystem === null)
        {
            self::$_fileSystem = new Filesystem();
        }

        return self::$_fileSystem;
    }

    /**
     * 复制文件
     *
     * @param string $originFile
     * @param string $targetFile
     * @param bool   $override
     */
    public static function copy($originFile, $targetFile, $override = false)
    {
        self::ensureFileSystem()->copy($originFile, $targetFile, $override);
    }

    /**
     * 删除指定文件
     *
     * @param $files
     */
    public static function delete($files)
    {
        self::ensureFileSystem()->remove($files);
    }
}

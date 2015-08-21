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
     * 创建目录
     *
     * @param mixed $dirs
     * @param int   $mode
     */
    public static function mkdir($dirs, $mode = 0777)
    {
        self::ensureFileSystem()->mkdir($dirs, $mode);
    }

    /**
     * 检测文件是否存在
     *
     * @param $files
     * @return bool
     */
    public static function exists($files)
    {
        return self::ensureFileSystem()->exists($files);
    }

    /**
     * touch创建一个空文件
     *
     * @param      $files
     * @param null $time
     * @param null $atime
     */
    public static function touch($files, $time = null, $atime = null)
    {
        self::ensureFileSystem()->touch($files, $time, $atime);
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

    /**
     * chmod
     *
     * @param      $files
     * @param      $mode
     * @param int  $umask
     * @param bool $recursive
     */
    public static function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        self::ensureFileSystem()->chmod($files, $mode, $umask, $recursive);
    }

    /**
     * @param      $files
     * @param      $user
     * @param bool $recursive
     */
    public static function chown($files, $user, $recursive = false)
    {
        self::ensureFileSystem()->chown($files, $user, $recursive);
    }

    /**
     * 重命名指定文件
     *
     * @param      $origin
     * @param      $target
     * @param bool $overwrite
     */
    public static function rename($origin, $target, $overwrite = false)
    {
        self::ensureFileSystem()->rename($origin, $target, $overwrite);
    }

    /**
     * @param $endPath
     * @param $startPath
     * @return string
     */
    public static function makePathRelative($endPath, $startPath)
    {
        return self::ensureFileSystem()->makePathRelative($endPath, $startPath);
    }

    /**
     * 写入文件
     *
     * @param string $filename
     * @param mixed  $content
     * @param int    $mode
     */
    public static function write($filename, $content, $mode = 0666)
    {
        self::ensureFileSystem()->dumpFile($filename, $content, $mode);
    }

    /**
     * 读取指定文件
     *
     * @param string $filename
     * @return bool|string
     */
    public static function read($filename)
    {
        if ( ! file_exists($filename))
        {
            return false;
        }

        return file_get_contents($filename);
    }

    /**
     * 修正目录路径
     *
     * @param $path
     * @return string
     */
    public static function fixFolderPath($path)
    {
        if ( ! empty($path))
        {
            $path = rtrim($path, '/') . '/';
        }

        return $path;
    }

    /**
     * 获取指定目录的上级目录
     *
     * @param $path
     * @return string
     */
    public static function getParentFolder($path)
    {
        $path = rtrim($path, '/'); // may be a folder

        $marker = strrpos($path, '/');

        if (false === $marker)
        {
            return '';
        }

        return substr($path, 0, $marker);
    }

    /**
     * 获取路径中的文件名
     *
     * @param $path
     * @return string
     */
    public static function getNameFromPath($path)
    {
        $path = rtrim($path, '/'); // may be a folder
        if (empty($path))
        {
            return '.';
        } // self directory

        $marker = strrpos($path, '/');
        if (false === $marker)
        {
            return $path;
        }

        return substr($path, $marker + 1);
    }

    /**
     * 获取文件扩展名
     *
     * @param string $path
     * @return string
     */
    public static function ext($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 字节数可视化阅读
     *
     * @param int $filesize
     * @return string
     */
    public static function sizeCount($filesize)
    {
        if ($filesize >= 1073741824)
        {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        }
        elseif ($filesize >= 1048576)
        {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        }
        elseif ($filesize >= 1024)
        {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        }
        else
        {
            $filesize = $filesize . ' Bytes';
        }
        return $filesize;
    }
}

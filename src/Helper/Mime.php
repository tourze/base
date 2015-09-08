<?php

namespace tourze\Base\Helper;

use finfo;
use tourze\Base\Config;

/**
 * MIME助手类
 *
 * @package tourze\Base\Helper
 */
class Mime extends HelperBase implements HelperInterface
{

    /**
     * @var string 保存MIME信息的配置文件
     */
    protected static $configName = 'helper/mime';

    /**
     * @var array 保存一个包含了后缀和对应mime的数组
     */
    protected static $mimeTypes = [];

    /**
     * 设置当前要加载的configName
     *
     * @param string $configName
     */
    public static function setConfigName($configName)
    {
        if (self::$configName != $configName)
        {
            self::$mimeTypes = [];
        }
        self::$configName = $configName;
    }

    /**
     * 获取当前要加载的配置名
     *
     * @return string
     */
    public static function getConfigName()
    {
        return self::$configName;
    }

    /**
     * 设置mime数组
     *
     * @param array $mimeTypes
     */
    public static function setMimeTypes($mimeTypes)
    {
        self::$mimeTypes = $mimeTypes;
    }

    /**
     * 获取当前加载到得mime数组
     *
     * @return array
     */
    public static function getMimeTypes()
    {
        return self::$mimeTypes;
    }

    /**
     * 初始化，和更新数据数组
     */
    public static function initMimeTypes()
    {
        if (empty(self::$mimeTypes))
        {
            self::setMimeTypes(Config::load(self::getConfigName())->asArray());
        }
    }

    /**
     * 根据文件名判断MIME
     *
     * @param string $file
     * @return string
     */
    public static function getMimeFromFile($file)
    {
        $ext = end(explode('.', $file));
        return self::getMimeFromExtension($ext);
    }

    /**
     * 根据MIME获得对应的后缀名
     * [!!] 注意，因为存在多个后缀对应一个MIME，所以返回的结果不一定是你要的
     *
     * @param string $mime
     * @param bool   $all
     * @return array|string
     */
    public static function getExtensionsFromMime($mime, $all = false)
    {
        self::initMimeTypes();

        $result = $all ? [] : '';

        foreach (self::$mimeTypes as $ext => $mimeType)
        {
            if ($mime == $mimeType)
            {
                if ($all)
                {
                    $result[] = $ext;
                }
                else
                {
                    $result = $ext;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 根据扩展名获取对应的MIME类型
     *
     * @param string $ext
     * @param string $default
     * @return string
     */
    public static function getMimeFromExtension($ext, $default = 'application/octet-stream')
    {
        self::initMimeTypes();
        return Arr::get(self::$mimeTypes, $ext, $default);
    }

    /**
     * 根据MIME获取对应的Content-Type
     *
     * @param string $ext
     * @param string $content
     * @param string $local_file
     * @param string $default
     * @return string
     */
    public static function determineContentType($ext = '', $content = '', $local_file = '', $default = '')
    {
        self::initMimeTypes();

        $defaultMime = 'application/octet-stream';

        if ( ! empty($default))
        {
            $defaultMime = $default;
        }

        $mime = '';

        if (class_exists('finfo'))
        {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            if ( ! empty($content))
            {
                $mime = $fileInfo->buffer($content);
            }
            elseif ( ! empty($local_file))
            {
                $mime = $fileInfo->file($local_file);
            }
        }

        if (empty($mime) ||
            (0 === strcasecmp($mime, $defaultMime)) ||
            (0 === strcasecmp('text/plain', $mime)) ||
            (0 === strcasecmp('text/x-asm', $mime)) ||
            (0 === strcasecmp('text/x-c', $mime)) ||
            (0 === strcasecmp('text/x-c++', $mime)) ||
            (0 === strcasecmp('text/x-java', $mime))
        )
        {
            if (0 === strcasecmp('dfpkg', $ext))
            {
                $mime = 'application/zip';
            }
            else
            {
                $mime = Arr::get(self::$mimeTypes, $ext);
            }
        }
        if (empty($mime))
        {
            return $defaultMime;
        }

        return $mime;
    }
}

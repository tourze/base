<?php

namespace tourze\Base\Helper;

use finfo;
use tourze\Base\Config;

/**
 * MIME助手类
 *
 * @package tourze\Base\Helper
 */
class Mime
{

    /**
     * 保存MIME信息的配置文件
     *
     * @var string
     */
    public static $configName = 'helper/mime';

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
     *
     * @param  string $mime
     * @return array|string
     */
    public static function getExtensionsFromMime($mime)
    {
        $mimeTypes = Config::load(self::$configName)->asArray();

        foreach ($mimeTypes as $ext => $mimeType)
        {
            if ($mime == $mimeType)
            {
                return $ext;
            }
        }

        return '';
    }

    /**
     * 根据扩展名获取对应的MIME类型
     *
     * @param string $ext
     * @return string
     */
    public static function getMimeFromExtension($ext)
    {
        $mimeTypes = Config::load(self::$configName)->asArray();

        return Arr::get($mimeTypes, $ext, 'application/octet-stream');
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
        $mimeTypes = Config::load(self::$configName)->asArray();

        $defaultMime = 'application/octet-stream';

        if ( ! empty($default))
        {
            $defaultMime = $default;
        }

        $mime = '';

        if (class_exists('finfo'))
        {
            $file_info = new finfo(FILEINFO_MIME_TYPE);
            if ( ! empty($content))
            {
                $mime = $file_info->buffer($content);
            }
            elseif ( ! empty($local_file))
            {
                $mime = $file_info->file($local_file);
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
            // need further guidance on these, as they are sometimes incorrect
            if (0 === strcasecmp('dfpkg', $ext))
            {
                $mime = 'application/zip';
            }
            else
            {
                $mime = Arr::get($mimeTypes, $ext);
            }
        }
        if (empty($mime))
        {
            return $defaultMime;
        }

        return $mime;
    }
}

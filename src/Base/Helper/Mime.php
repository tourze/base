<?php

namespace tourze\Base\Helper;
use tourze\Base\Config;

/**
 * MIME助手类
 *
 * @package tourze\Base\Helper
 */
class Mime
{

    public static $configName = 'helper/mime';

    /**
     * @param $file
     * @return string
     */
    public static function getMimeFromFile($file)
    {
        $ext = end(explode('.', $file));
        return self::getMimeFromExtension($ext);
    }

    /**
     * @param      $mime
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
     * @param $ext
     * @return string
     */
    public static function getMimeFromExtension($ext)
    {
        $mimeTypes = Config::load(self::$configName)->asArray();

        return Arr::get($mimeTypes, $ext, 'application/octet-stream');
    }

    /**
     * @param string $ext
     * @param string $content
     * @param string $local_file
     * @param string $default
     *
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
            $file_info = new \finfo(FILEINFO_MIME_TYPE);
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

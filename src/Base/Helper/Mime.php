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

}

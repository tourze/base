<?php

namespace tourze\Base\Helper;

use Hoa\Mime\Mime as HoaMime;

/**
 * MIME助手类
 *
 * @package tourze\Base\Helper
 */
class Mime
{

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
     * @param bool $combine
     * @return array|string
     * @throws \Hoa\Mime\Exception\MimeIsNotFound
     */
    public static function getExtensionsFromMime($mime, $combine = true)
    {
        $result = HoaMime::getExtensionsFromMime($mime);

        return $combine ? implode('/', $result) : $result;
    }

    /**
     * @param $ext
     * @return string
     */
    public static function getMimeFromExtension($ext)
    {
        return HoaMime::getMimeFromExtension($ext);
    }

}

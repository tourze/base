<?php

namespace tourze\Base\Helper;

class Asset
{

    /**
     * @var string 第三方资源地址
     */
    public static $assetHost = null;

    /**
     * 读取第三方资源地址
     *
     * @return string
     */
    public static function assetHost()
    {
        if (self::$assetHost === null)
        {
            self::$assetHost = 'http://asset.tourze.com/';
            if (strpos($_SERVER['HTTP_HOST'], 'test.') !== false)
            {
                self::$assetHost = 'http://test.asset.tourze.com/';
            }
            if (strpos($_SERVER['HTTP_HOST'], 'local.') !== false)
            {
                self::$assetHost = 'http://local.asset.tourze.com/';
            }
        }

        return self::$assetHost;
    }

    /**
     * 返回完整的url
     *
     * @param $uri
     * @return string
     */
    public static function url($uri)
    {
        return self::assetHost() . $uri;
    }
}

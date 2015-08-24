<?php

namespace tourze\Base;

use Noodlehaus\Config as VendorConfig;

/**
 * 配置管理器
 *
 * @package tourze\Base
 */
class Config extends VendorConfig
{

    /**
     * @var array
     */
    protected static $_pathCache = [];

    /**
     * @var array 允许从其中加载配置文件
     */
    protected static $_configPaths = [];

    /**
     * 增加配置加载目录
     *
     * @param $path
     */
    public static function addPath($path)
    {
        if ( ! isset(self::$_configPaths[$path]))
        {
            self::$_configPaths[$path] = $path;
        }
    }

    /**
     * 继承原来的load方法，实现级联系统的配置文件自动加载
     *
     * @param array|string $path 缓存文件路径，同时也是缓存的key
     * @param bool         $reload
     * @return static
     */
    public static function load($path, $reload = false)
    {
        if ( ! is_array($path))
        {
            $path = [$path];
        }

        $cacheKey = md5(json_encode($path));
        if ( ! $reload && isset(self::$_pathCache[$cacheKey]))
        {
            return self::$_pathCache[$cacheKey];
        }

        $finalPath = [];
        foreach ($path as $_path)
        {
            foreach (self::$_configPaths as $includePath)
            {
                // 检测和包含带下面后缀的文件
                $files = [
                    $includePath . $_path . '.php',
                    $includePath . $_path . '-local.php',
                ];
                foreach ($files as $file)
                {
                    if (strpos($file, '*') === false)
                    {
                        if (is_file($file))
                        {
                            $finalPath[] = $file;
                        }
                    }
                    else
                    {
                        $finalPath[] = $file;
                    }
                }
            }
        }

        return self::$_pathCache[$cacheKey] = parent::load($finalPath);
    }

    /**
     * 返回当前配置的完整数组
     *
     * @return array
     */
    public function asArray()
    {
        return (array) $this->data;
    }
}

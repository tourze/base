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
     * @var array 允许从其中加载配置文件
     */
    public static $configDirectories = [];

    /**
     * 增加配置加载目录
     *
     * @param $path
     */
    public static function addDirectory($path)
    {
        self::$configDirectories[] = $path;
    }

    /**
     * 继承原来的load方法，实现级联系统的配置文件自动加载
     *
     * @param array|string $path
     * @return \Noodlehaus\Config
     */
    public static function load($path)
    {
        if ( ! is_array($path))
        {
            $path = [$path];
        }

        $finalPath = [];
        foreach ($path as $_path)
        {
            foreach (self::$configDirectories as $includePath)
            {
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

        return parent::load($finalPath);
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

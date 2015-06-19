<?php

namespace tourze\Composer;

use Composer\Script\Event;
use tourze\Base\Helper\File;

class Composer
{

    public static $packageDir = 'tourze/core';

    public static function postInstall(Event $event)
    {
        self::removeDotGitDirectory($event);
    }

    public static function postUpdate(Event $event)
    {
        self::removeDotGitDirectory($event);
    }

    /**
     * 删除指定目录中的.git
     *
     * @param \Composer\Script\Event $event
     */
    public static function removeDotGitDirectory(Event $event)
    {
        $composer = $event->getComposer();

        $gitDir = $composer->getConfig()->get('vendor-dir') . '/' . self::$packageDir . '/.git';

        echo "checking $gitDir\n";
        if (is_dir($gitDir))
        {
            echo "removing $gitDir\n";
            File::delete($gitDir);
        }
    }
}

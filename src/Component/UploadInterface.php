<?php

namespace tourze\Base\Component;

use tourze\Base\Component\Upload\Entity;
use tourze\Base\ComponentInterface;

/**
 * 上传处理接口
 *
 * @package tourze\Base\Component
 */
interface UploadInterface extends ComponentInterface
{

    /**
     * 获取指定key的上传文件
     *
     * @param string $name
     * @return bool|Entity
     */
    public function get($name);

    /**
     * 获取所有已经上传的文件
     *
     * @return array
     */
    public function all();
}

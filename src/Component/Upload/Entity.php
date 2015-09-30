<?php

namespace tourze\Base\Component\Upload;

use tourze\Base\Object;

/**
 * 单个上传文件的实例
 *
 * @package tourze\Base\Component\Upload
 */
class Entity extends Object
{

    /**
     * @var string 文件名
     */
    public $name;

    /**
     * @var string 文件类型
     */
    public $type;

    /**
     * @var string 文件路径
     */
    public $path;

    /**
     * @var int 错误？
     */
    public $error = 0;

    /**
     * @var int 文件字节数
     */
    public $size = 0;
}

<?php

namespace tourze\Base\Component;

use tourze\Base\Component;
use tourze\Base\Component\Upload\Entity;
use tourze\Base\Helper\Arr;

/**
 * 上传处理
 *
 * @package tourze\Base\Component
 */
class Upload extends Component implements UploadInterface
{

    /**
     * {@inheritdoc}
     */
    public $persistence = false;

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ( ! isset($_FILES[$name]))
        {
            return false;
        }

        return new Entity([
            'name'  => Arr::get($_FILES[$name], 'name'),
            'type'  => Arr::get($_FILES[$name], 'type'),
            'path'  => Arr::get($_FILES[$name], 'tmp_name'),
            'error' => Arr::get($_FILES[$name], 'error', 0),
            'size'  => Arr::get($_FILES[$name], 'size', 0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return array_keys($_FILES);
    }
}

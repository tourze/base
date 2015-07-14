<?php

namespace tourze\Security\Password\Hash;

use tourze\Security\Password\Hash;
use tourze\Security\Password\HashInterface;

/**
 * 两次md5加密
 *
 * @package tourze\Security\Password\Hash
 */
class MD5Twice extends Hash implements HashInterface
{

    /**
     * @var string 明文
     */
    public $text;

    public function hash()
    {
        return (string) md5(md5($this->text));
    }
}

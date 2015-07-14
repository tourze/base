<?php

namespace tourze\Security\Password\Hash;

use tourze\Security\Password\Hash;
use tourze\Security\Password\HashInterface;

/**
 * Joomla加密方式
 *
 * @package tourze\Security\Password\Hash
 */
class Joomla extends Hash implements HashInterface
{

    /**
     * @var string 明文
     */
    public $text;

    public $salt = '';

    public function hash()
    {
        return (string) md5($this->text . $this->salt);
    }
}

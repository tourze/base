<?php

namespace tourze\Security;

use tourze\Base\Helper\Arr;
use ZxcvbnPhp\Zxcvbn;

/**
 * 密码相关的安全方法
 *
 * @package tourze\Security
 */
class Password
{

    /**
     * 默认密码加密方式
     */
    const DEFAULT_HASH_TYPE = 'md5';

    /**
     * 检测输入密码的强壮程度，并返回检测结果，结果越大，密码就越健壮
     *
     * @param $password
     * @return int
     */
    public static function strength($password)
    {
        $zxcvbn = new Zxcvbn();
        $result = $zxcvbn->passwordStrength($password);

        return (int) Arr::get($result, 'score');
    }

    /**
     * @param $input
     * @param $hashType
     */
    public static function hash($input, $hashType = self::DEFAULT_HASH_TYPE)
    {
    }
}

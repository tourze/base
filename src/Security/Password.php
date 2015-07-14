<?php

namespace tourze\Security;

use tourze\Base\Helper\Arr;
use tourze\Security\Password\Hash\Joomla;
use tourze\Security\Password\Hash\MD5;
use tourze\Security\Password\Hash\MD5Twice;
use tourze\Security\Password\Hash\Plain;
use tourze\Security\Password\Hash\SHA1;
use ZxcvbnPhp\Zxcvbn;

/**
 * 密码相关的安全方法
 *
 * @package tourze\Security
 */
class Password
{

    /**
     * 默认密码加密方式，最简单的MD5
     */
    const MD5_HASH = 'md5';

    /**
     * 简单的sha1加密
     */
    const SHA1_HASH = 'sha1';

    /**
     * 双重md5
     */
    const MD5_TWICE_HASH = 'md5_md5';

    /**
     * md5($text.$salt)，Joomla采用这个方式
     */
    const PASS_SALT_MD5_HASH = 'pass_salt_md5';

    /**
     * md5($salt.$text)，osCommerce的加密方式
     */
    const SALT_PASS_MD5_HASH = 'salt_pass_md5';

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
     * @param string $text     明文
     * @param string $hashType 加密方式
     * @param array  $extra    附加参数
     * @return string
     */
    public static function hash($text, $hashType = self::MD5_HASH, array $extra = null)
    {
        $params = Arr::merge(['text' => $text], (array) $extra);
        switch ($hashType)
        {
            case self::MD5_HASH:
                $object = new MD5($params);
                break;
            case self::SHA1_HASH:
                $object = new SHA1($params);
                break;
            case self::MD5_TWICE_HASH:
                $object = new MD5Twice($params);
                break;
            case self::PASS_SALT_MD5_HASH:
                $object = new Joomla($params);
                break;
            default:
                $object = new Plain($params);
        }

        return $object->hash();
    }
}

<?php

namespace tourze\Base\Helper;

use tourze\Session\Session;

/**
 * 读取用户信息的另外一种方式，会有安全隐患
 *
 * @package tourze\Base\Helper
 */
class User
{

    /**
     * 用户数据
     *
     * @var array
     */
    protected static $_data = [];

    public static $userDataKey = 'tourze_uc_user';

    public static $encryptKey = 'tourze_uc_user';

    /**
     * 获取用户key值
     *
     * @param null $key
     * @param null $default
     * @return array|mixed|void
     */
    public static function get($key = null, $default = null)
    {
        $data = self::getData();

        if ( ! $key)
        {
            return $data;
        }

        return isset($data[$key]) ? $data[$key] : $default;
    }

    /**
     * 读取用户数据
     *
     * @return array|mixed
     */
    public static function getData()
    {
        if (empty(self::$_data))
        {
            $user = [];

            // 先尝试直接读session
            if (empty($user) && $session = Session::instance()->get(self::$userDataKey))
            {
                $user = json_decode($user, true);
            }

            // 尝试读cookie
            if (empty($user) && isset($_COOKIE[self::$userDataKey]))
            {
                $user = self::decrypt($_COOKIE[self::$userDataKey]);
                $user = json_decode($user, true);
            }

            self::$_data = $user;
        }

        return self::$_data;
    }

    /**
     * 设置用户数据
     *
     * @param array $data
     */
    public static function setData($data)
    {
        $data = (array) $data;
        $data = json_encode($data);

        // 先保存到session
        Session::instance()->set(self::$userDataKey, $data);

        // 再保存到cookie
        $cookieData = self::encrypt($data);
        setcookie(self::$userDataKey, $cookieData);
    }

    /**
     * 加密数据
     *
     * @param $input
     * @return string
     */
    protected static function encrypt($input)
    {
        return AuthCode::encode($input, self::$encryptKey);
    }

    /**
     * 解密数据
     *
     * @param $input
     * @return string
     */
    protected static function decrypt($input)
    {
        return AuthCode::decode($input, self::$encryptKey);
    }
}

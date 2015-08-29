<?php

namespace tourze\Base\Helper;

use tourze\Base\Base;
use tourze\Base\Exception\HelperException;

/**
 * Cookie助手类
 *
 * @package    Base
 * @category   Helpers
 * @author     YwiSax
 */
class Cookie
{

    /**
     * @var  string  用于混淆cookie的字符串
     */
    public static $salt = null;

    /**
     * @var int 默认过期时间
     */
    public static $expiration = 0;

    /**
     * @var string 默认作用路径
     */
    public static $path = '/';

    /**
     * @var string 默认作用域
     */
    public static $domain = null;

    /**
     * @var bool 是否只通过https传递cookie
     */
    public static $secure = false;

    /**
     * @var bool 只允许http/https访问，禁止Javascript访问
     */
    public static $httpOnly = false;

    /**
     * 获取指定key的cookie值，否则返回默认值
     *
     * @param  string $key     cookie名
     * @param  mixed  $default 默认值
     * @return string
     */
    public static function get($key, $default = null)
    {
        if ( ! isset($_COOKIE[$key]))
        {
            return $default;
        }

        $cookie = $_COOKIE[$key];
        // 分离salt和内容
        $split = strlen(Cookie::salt($key, null));

        if (isset($cookie[$split]) && $cookie[$split] === '~')
        {
            list ($hash, $value) = explode('~', $cookie, 2);

            if (Cookie::salt($key, $value) === $hash)
            {
                // 检查hash是否正确
                return $value;
            }
            // 不正确的话，那就删除cookie
            Cookie::delete($key);
        }

        return $default;
    }

    /**
     * 设置一个cookie
     *
     *     Cookie::set('theme', 'red');
     *
     * @param  string $name    cookie名
     * @param  string $value   cookie值
     * @param  int    $expired 生效时间（秒数）
     * @return bool
     */
    public static function set($name, $value, $expired = null)
    {
        // 处理过期时间
        if (null === $expired)
        {
            $expired = Cookie::$expiration;
        }
        if ($expired !== 0)
        {
            $expired += time();
        }

        // Add the salt to the cookie value
        $value = Cookie::salt($name, $value) . '~' . $value;

        return Base::getHttp()->setCookie($name, $value, $expired, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httpOnly);
    }

    /**
     * 删除指定的cookie
     *
     *     Cookie::delete('theme');
     *
     * @param  string $name cookie名
     * @return bool
     */
    public static function delete($name)
    {
        unset($_COOKIE[$name]);
        return Base::getHttp()->setCookie($name, null, -86400, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httpOnly);
    }

    /**
     * 根据cookie名和内容，生成一个salt
     *
     *     $salt = Cookie::salt('theme', 'red');
     *
     * @param  string $name  cookie名
     * @param  string $value cookie值
     * @return string
     * @throws HelperException
     */
    public static function salt($name, $value)
    {
        // Determine the user agent
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent . $name . $value . Cookie::$salt);
    }

}

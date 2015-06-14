<?php

namespace tourze\Base\Helper;

use tourze\Base\Exception\HelperException;

/**
 * CookieHelper helper.
 *
 * @package    Base
 * @category   Helpers
 * @author     YwiSax
 */
class Cookie
{

    /**
     * @var  string  Magic salt to add to the cookie
     */
    public static $salt = null;

    /**
     * @var  integer  NumberHelper of seconds before the cookie expires
     */
    public static $expiration = 0;

    /**
     * @var  string  Restrict the path that the cookie is available to
     */
    public static $path = '/';

    /**
     * @var  string  Restrict the domain that the cookie is available to
     */
    public static $domain = null;

    /**
     * @var  boolean  Only transmit cookies over secure connections
     */
    public static $secure = false;

    /**
     * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
     */
    public static $httpOnly = false;

    /**
     * Gets the value of a signed cookie. Cookies without signatures will not
     * be returned. If the cookie signature is present, but invalid, the cookie
     * will be deleted.
     *
     *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
     *     $theme = CookieHelper::get('theme', 'blue');
     *
     * @param   string $key     cookie name
     * @param   mixed  $default default value to return
     * @return  string
     */
    public static function get($key, $default = null)
    {
        if ( ! isset($_COOKIE[ $key ]))
        {
            // The cookie does not exist
            return $default;
        }

        // Get the cookie value
        $cookie = $_COOKIE[ $key ];

        // Find the position of the split between salt and contents
        $split = strlen(Cookie::salt($key, null));

        if (isset($cookie[ $split ]) && $cookie[ $split ] === '~')
        {
            // Separate the salt and the value
            list ($hash, $value) = explode('~', $cookie, 2);

            if (Cookie::salt($key, $value) === $hash)
            {
                // CookieHelper signature is valid
                return $value;
            }

            // The cookie signature is invalid, delete it
            Cookie::delete($key);
        }

        return $default;
    }

    /**
     * 设置一个cookie
     *
     *     Cookie::set('theme', 'red');
     *
     * @param   string  $name       name of cookie
     * @param   string  $value      value of cookie
     * @param   integer $expiration lifetime in seconds
     * @return  boolean
     */
    public static function set($name, $value, $expiration = null)
    {
        if (null === $expiration)
        {
            // Use the default expiration
            $expiration = Cookie::$expiration;
        }

        if ($expiration !== 0)
        {
            // The expiration is expected to be a UNIX timestamp
            $expiration += time();
        }

        // Add the salt to the cookie value
        $value = Cookie::salt($name, $value) . '~' . $value;

        return setcookie($name, $value, $expiration, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httpOnly);
    }

    /**
     * Deletes a cookie by making the value null and expiring it.
     *
     *     CookieHelper::delete('theme');
     *
     * @param   string $name cookie name
     * @return  boolean
     */
    public static function delete($name)
    {
        // Remove the cookie
        unset($_COOKIE[ $name ]);

        // Nullify the cookie and make it expire
        return setcookie($name, null, -86400, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httpOnly);
    }

    /**
     * Generates a salt string for a cookie based on the name and value.
     *
     *     $salt = CookieHelper::salt('theme', 'red');
     *
     * @param   string $name  name of cookie
     * @param   string $value value of cookie
     * @return  string
     * @throws  HelperException
     */
    public static function salt($name, $value)
    {
        // Determine the user agent
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent . $name . $value . Cookie::$salt);
    }

}

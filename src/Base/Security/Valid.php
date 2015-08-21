<?php

namespace tourze\Base\Security;

use ArrayObject;
use Respect\Validation\Validator;

/**
 * 校验助手方法
 *
 * @package    Base
 * @category   Security
 * @author     YwiSax
 */
class Valid
{

    /**
     * @var array 信用卡规则
     */
    public static $creditCardRules = [
        'default'          => [
            'length' => '13,14,15,16,17,18,19',
            'prefix' => '',
            'luhn'   => true,
        ],

        'american express' => [
            'length' => '15',
            'prefix' => '3[47]',
            'luhn'   => true,
        ],

        'diners club'      => [
            'length' => '14,16',
            'prefix' => '36|55|30[0-5]',
            'luhn'   => true,
        ],

        'discover'         => [
            'length' => '16',
            'prefix' => '6(?:5|011)',
            'luhn'   => true,
        ],

        'jcb'              => [
            'length' => '15,16',
            'prefix' => '3|1800|2131',
            'luhn'   => true,
        ],

        'maestro'          => [
            'length' => '16,18',
            'prefix' => '50(?:20|38)|6(?:304|759)',
            'luhn'   => true,
        ],

        'mastercard'       => [
            'length' => '16',
            'prefix' => '5[1-5]',
            'luhn'   => true,
        ],

        'visa'             => [
            'length' => '13,16',
            'prefix' => '4',
            'luhn'   => true,
        ],
    ];

    /**
     * 检测输入是否为数组
     *
     * @param mixed $value
     * @return bool
     */
    public static function isArray($value)
    {
        return Validator::arr()->validate($value);
    }

    /**
     * 检查数组是否包含指定的值
     *
     * @param      $needle
     * @param      $array
     * @param null $strict
     * @return bool
     */
    public static function inArray($needle, array $array, $strict = null)
    {
        return in_array($needle, $array, $strict);
    }

    /**
     * 检查传入参数是否不为空
     *
     * @param   $value
     *
     * @return  bool
     */
    public static function notEmpty($value)
    {
        if (is_object($value) && $value instanceof ArrayObject)
        {
            // Get the array from the ArrayObject
            $value = $value->getArrayCopy();
        }

        // Value cannot be null, false, '', or an empty array
        return ! in_array($value, [
            null,
            false,
            '',
            []
        ], true);
    }

    /**
     * Checks a field against a regular expression.
     *
     * @param   string $value      value
     * @param   string $expression regular expression to match (including delimiters)
     *
     * @return  boolean
     */
    public static function regex($value, $expression)
    {
        return Validator::regex($expression)->validate($value);
    }

    /**
     * 是否满足最短长度要求
     *
     * @param   string  $value  value
     * @param   int $length minimum length required
     *
     * @return  boolean
     */
    public static function minLength($value, $length)
    {
        return Validator::string()->length($length, null)->validate($value);
    }

    /**
     * 是否满足最大长度要求
     *
     * @param   string  $value  value
     * @param   int $length maximum length required
     *
     * @return  boolean
     */
    public static function maxLength($value, $length)
    {
        return Validator::string()->length(null, $length)->validate($value);
    }

    /**
     * 检测是否为指定长度
     *
     * @param   string        $value  value
     * @param   integer|array $length exact length required, or array of valid lengths
     *
     * @return  boolean
     */
    public static function exactLength($value, $length)
    {
        if ( ! is_array($length))
        {
            $length = [$length];
        }

        foreach ($length as $strlen)
        {
            if (strlen($value) === $strlen)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks that a field is exactly the value required.
     *
     * @param   string $value    value
     * @param   string $required required value
     * @param bool     $strict
     * @return bool
     */
    public static function equals($value, $required, $strict = false)
    {
        return Validator::equals($value, $strict)->validate($required);
    }

    /**
     * Check an email address for correct format.
     *
     * @link  http://www.iamcal.com/publish/articles/php/parsing_email/
     * @link  http://www.w3.org/Protocols/rfc822/
     * @param   string  $email  email address
     * @return  boolean
     */
    public static function email($email)
    {
        return Validator::email()->validate($email);
    }

    /**
     * Validate the domain of an email address by checking if the domain has a valid MX record.
     *
     * @link  http://php.net/checkdnsrr  not added to Windows until PHP 5.3.0
     * @param   string $email email address
     * @return  boolean
     */
    public static function emailDomain($email)
    {
        if ( ! self::notEmpty($email))
        {
            return false;
        }

        // 检查MX记录
        return (bool) checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
    }

    /**
     * 是否为正确的URL
     *
     * @param   string $url URL
     * @return  boolean
     */
    public static function url($url)
    {
        return Validator::url()->validate($url);
    }

    /**
     * 是否为合格的IP地址
     *
     * @param   string  $ip           IP address
     * @param   boolean $allowPrivate allow private IP networks
     *
     * @return  boolean
     */
    public static function ip($ip, $allowPrivate = true)
    {
        return $allowPrivate
            ? Validator::ip()->validate($ip)
            : Validator::ip(FILTER_FLAG_NO_PRIV_RANGE)->validate($ip);
    }

    /**
     * 检验输入的数据是否为合适的信用卡号码
     *
     * @param   int      $number credit card number
     * @return  boolean
     */
    public static function creditCard($number)
    {
        return Validator::creditCard()->validate($number);
    }

    /**
     * Checks if a phone number is valid.
     *
     * @param   string $number phone number to check
     * @return  boolean
     */
    public static function phone($number)
    {
        return Validator::phone()->validate($number);
    }

    /**
     * Tests if a string is a valid date string.
     *
     * @param   string $input date to check
     * @return  boolean
     */
    public static function date($input)
    {
        return Validator::date()->validate($input);
    }

    /**
     * Checks whether a string consists of alphabetical characters only.
     *
     * @param   string  $input  input string
     * @return  boolean
     */
    public static function alpha($input)
    {
        return Validator::alpha()->validate($input);
    }

    /**
     * Checks whether a string consists of alphabetical characters and numbers only.
     *
     * @param   string  $str  input string
     * @param   boolean $utf8 trigger UTF-8 compatibility
     *
     * @return  boolean
     */
    public static function alphaNumeric($str, $utf8 = false)
    {
        if (true === $utf8)
        {
            return (bool) preg_match('/^[\pL\pN]++$/uD', $str);
        }
        else
        {
            return ctype_alnum($str);
        }
    }

    /**
     * Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only.
     *
     * @param   string  $str  input string
     * @param   boolean $utf8 trigger UTF-8 compatibility
     *
     * @return  boolean
     */
    public static function alphaDash($str, $utf8 = false)
    {
        if (true === $utf8)
        {
            $regex = '/^[-\pL\pN_]++$/uD';
        }
        else
        {
            $regex = '/^[-a-z0-9_]++$/iD';
        }

        return (bool) preg_match($regex, $str);
    }

    /**
     * Checks whether a string consists of digits only (no dots or dashes).
     *
     * @param   string  $input  input string
     * @return  boolean
     */
    public static function digit($input)
    {
        return Validator::digit()->validate($input);
    }

    /**
     * 检查输入字符串是否为有效数字（正负数都可以）
     *
     * @param   string $input input string
     * @return  boolean
     */
    public static function numeric($input)
    {
        return Validator::numeric()->validate($input);
    }

    /**
     * 是否为非负数
     *
     * @param string $value
     * @return bool
     */
    public static function notNegative($value)
    {
        return ! Validator::numeric()->negative()->validate($value);
    }

    /**
     * Tests if a number is within a range.
     *
     * @param   string  $number number to check
     * @param   int $min    minimum value
     * @param   int $max    maximum value
     * @return  boolean
     */
    public static function range($number, $min, $max)
    {
        return Validator::int()->between($min, $max)->validate($number);
    }

    /**
     * Checks if a string is a proper decimal format. Optionally, a specific
     * number of digits can be checked too.
     *
     * @param   string  $str    number to check
     * @param   int $places number of decimal places
     * @param   int $digits number of digits
     * @return  boolean
     */
    public static function decimal($str, $places = 2, $digits = null)
    {
        if ($digits > 0)
        {
            // Specific number of digits
            $digits = '{' . ((int) $digits) . '}';
        }
        else
        {
            // Any number of digits
            $digits = '+';
        }

        // Get the decimal point for the current locale
        list($decimal) = array_values(localeconv());

        return (bool) preg_match('/^[+-]?[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . ((int) $places) . '}$/D', $str);
    }

    /**
     * Checks if a string is a proper hexadecimal HTML color value. The validation
     * is quite flexible as it does not require an initial "#" and also allows for
     * the short notation using only three instead of six hexadecimal characters.
     *
     * @param   string $str input string
     * @return  boolean
     */
    public static function color($str)
    {
        return Validator::hexRgbColor()->validate($str);
    }

    /**
     * 比较两个值是否相等
     *
     * @param $text1
     * @param $text2
     * @return bool
     */
    public static function match($text1, $text2)
    {
        return $text1 == $text2;
    }

    /**
     * Checks if a field matches the value of another field.
     *
     * @param   array  $array array of values
     * @param   string $field field name
     * @param   string $match field name to match
     * @return  boolean
     */
    public static function matches($array, $field, $match)
    {
        return ($array[$field] === $array[$match]);
    }

    /**
     * Check if a number is greater than
     *
     * @param   string $value
     * @param   mixed  $compare
     * @return  bool
     */
    public static function gt($value, $compare)
    {
        return $value > $compare;
    }

    /**
     * Check if a number is greater than or equal to
     *
     * @param   string $value
     * @param   mixed  $compare
     * @return  bool
     */
    public static function gte($value, $compare)
    {
        return $value >= $compare;
    }

    /**
     * Check if a number is less than
     *
     * @param   string $value
     * @param   mixed  $compare
     * @return  bool
     */
    public static function lt($value, $compare)
    {
        return $value < $compare;
    }

    /**
     * Check if a number is less than or equal to
     *
     * @param   string $value
     * @param   mixed  $compare
     * @return  bool
     */
    public static function lte($value, $compare)
    {
        return $value <= $compare;
    }
}

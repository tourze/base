<?php

namespace tourze\Base\Security;

use ArrayObject;
use Respect\Validation\Validator;

/**
 * 校验助手方法
 *
 * @package tourze\Base\Security
 */
class Valid
{

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
     * @param mixed $needle
     * @param array $array
     * @param bool  $strict
     * @return bool
     */
    public static function inArray($needle, array $array, $strict = null)
    {
        return in_array($needle, $array, $strict);
    }

    /**
     * 检查传入参数是否不为空
     *
     * @param  mixed $value
     * @return bool
     */
    public static function notEmpty($value)
    {
        if (is_object($value) && $value instanceof ArrayObject)
        {
            // 如果是个ArrayObject对象，那么先读取起内容
            $value = $value->getArrayCopy();
        }

        return ! in_array($value, [
            null,
            false,
            '',
            []
        ], true);
    }

    /**
     * 检查输入是否符合正则
     *
     * @param  string $value      要检查的值
     * @param  string $expression 正则表达式（包含分隔符）
     * @return bool
     */
    public static function regex($value, $expression)
    {
        return Validator::regex($expression)->validate($value);
    }

    /**
     * 是否满足最短长度要求
     *
     * @param  string $value
     * @param  int    $length 最小长度
     * @return bool
     */
    public static function minLength($value, $length)
    {
        return Validator::string()->length($length, null)->validate($value);
    }

    /**
     * 是否满足最大长度要求
     *
     * @param  string $value
     * @param  int    $length 最大长度
     * @return bool
     */
    public static function maxLength($value, $length)
    {
        return Validator::string()->length(null, $length)->validate($value);
    }

    /**
     * 检测是否为指定长度
     *
     * @param  string    $value
     * @param  int|array $length 指定长度，或者提供一个可选长度的数组
     * @return bool
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
     * 检测是否为特定值
     *
     * @param  string $value
     * @param  string $required 要比较的值
     * @param  bool   $strict
     * @return bool
     */
    public static function equals($value, $required, $strict = false)
    {
        return Validator::equals($value, $strict)->validate($required);
    }

    /**
     * 检测邮箱是否正确
     *
     * @link  http://www.iamcal.com/publish/articles/php/parsing_email/
     * @link  http://www.w3.org/Protocols/rfc822/
     * @param  string $email email地址
     * @return bool
     */
    public static function email($email)
    {
        return Validator::email()->validate($email);
    }

    /**
     * 通过检测邮箱的MX记录来判断邮箱地址是否正确
     *
     * @link  http://php.net/checkdnsrr
     * @param  string $value email地址
     * @return bool
     */
    public static function emailDomain($value)
    {
        if ( ! self::notEmpty($value))
        {
            return false;
        }

        // 检查MX记录
        return (bool) checkdnsrr(preg_replace('/^[^@]++@/', '', $value), 'MX');
    }

    /**
     * 是否为正确的URL
     *
     * @param  string $value URL
     * @return bool
     */
    public static function url($value)
    {
        return Validator::url()->validate($value);
    }

    /**
     * 是否为合格的IP地址
     *
     * @param  string $value        IP地址
     * @param  bool   $allowPrivate 是否允许私有IP地址
     * @return bool
     */
    public static function ip($value, $allowPrivate = true)
    {
        return $allowPrivate
            ? Validator::ip()->validate($value)
            : Validator::ip(FILTER_FLAG_NO_PRIV_RANGE)->validate($value);
    }

    /**
     * 检验输入的数据是否为合适的信用卡号码
     *
     * @param  int $value 信用卡号码
     * @return bool
     */
    public static function creditCard($value)
    {
        return Validator::creditCard()->validate($value);
    }

    /**
     * 检测手机号码是否正确
     *
     * @param  string $value 手机号码
     * @return bool
     */
    public static function phone($value)
    {
        return Validator::phone()->validate($value);
    }

    /**
     * 检测日期字符串是否正确
     *
     * @param  string $value
     * @return bool
     */
    public static function date($value)
    {
        return Validator::date()->validate($value);
    }

    /**
     * 检查字符串是否只包含字母
     *
     * @param  string $value
     * @return bool
     */
    public static function alpha($value)
    {
        return Validator::alpha()->validate($value);
    }

    /**
     * 检测字符串是否只包含了字母或数字
     *
     * @param  string $value
     * @param  bool   $utf8 UTF8兼容
     * @return bool
     */
    public static function alphaNumeric($value, $utf8 = false)
    {
        if (true === $utf8)
        {
            return (bool) preg_match('/^[\pL\pN]++$/uD', $value);
        }
        else
        {
            return ctype_alnum($value);
        }
    }

    /**
     * 检测字符串是否只包含了字母、数字、下划线或破折号
     *
     * @param  string $value
     * @param  bool   $utf8 UTF8兼容
     * @return bool
     */
    public static function alphaDash($value, $utf8 = false)
    {
        if (true === $utf8)
        {
            $regex = '/^[-\pL\pN_]++$/uD';
        }
        else
        {
            $regex = '/^[-a-z0-9_]++$/iD';
        }

        return (bool) preg_match($regex, $value);
    }

    /**
     * 检测字符串是否只有数字
     *
     * @param  string $value
     * @return bool
     */
    public static function digit($value)
    {
        return Validator::digit()->validate($value);
    }

    /**
     * 检查输入字符串是否为有效数字（正负数都可以）
     *
     * @param  string $value
     * @return bool
     */
    public static function numeric($value)
    {
        return Validator::numeric()->validate($value);
    }

    /**
     * 是否为非负数
     *
     * @param  string $value
     * @return bool
     */
    public static function notNegative($value)
    {
        return ! Validator::numeric()->negative()->validate($value);
    }

    /**
     * 检测数值是否在范围内
     *
     * @param  string $value
     * @param  int    $min 最小值
     * @param  int    $max 最大值
     * @return bool
     */
    public static function range($value, $min, $max)
    {
        return Validator::int()->between($min, $max)->validate($value);
    }

    /**
     * 检查字符串是否为适当的十进制格式。也可以检查特定数目的数字
     *
     * @param  string $value
     * @param  int    $places 小数点为
     * @param  int    $digits 数字位数
     * @return bool
     */
    public static function decimal($value, $places = 2, $digits = null)
    {
        $digits = $digits > 0
            ? '{' . ((int) $digits) . '}' // 指定位数
            : '+'; // 任意位数

        $decimal = array_shift(array_values(localeconv()));

        return (bool) preg_match('/^[+-]?[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . ((int) $places) . '}$/D', $value);
    }

    /**
     * 检测是否为合格的颜色表达值
     *
     * @param  string $value
     * @return bool
     */
    public static function color($value)
    {
        return Validator::hexRgbColor()->validate($value);
    }

    /**
     * 比较两个值是否相等
     *
     * @param  mixed $text1
     * @param  mixed $text2
     * @return bool
     */
    public static function match($text1, $text2)
    {
        return $text1 == $text2;
    }

    /**
     * 检测数值中的某个字段是否跟指定值匹配
     *
     * @param  array  $array
     * @param  string $field
     * @param  string $match 要匹配的值
     * @return bool
     */
    public static function matches($array, $field, $match)
    {
        return ($array[$field] === $array[$match]);
    }

    /**
     * 检查一个数字是否大于指定值
     *
     * @param  string $value
     * @param  mixed  $compare
     * @return bool
     */
    public static function gt($value, $compare)
    {
        return $value > $compare;
    }

    /**
     * 检查一个数字是否大于或等于指定值
     *
     * @param  string $value
     * @param  mixed  $compare
     * @return bool
     */
    public static function gte($value, $compare)
    {
        return $value >= $compare;
    }

    /**
     * 检查一个数字是否小于指定值
     *
     * @param  string $value
     * @param  mixed  $compare
     * @return bool
     */
    public static function lt($value, $compare)
    {
        return $value < $compare;
    }

    /**
     * 检查一个数字是否小于或等于指定值
     *
     * @param  string $value
     * @param  mixed  $compare
     * @return bool
     */
    public static function lte($value, $compare)
    {
        return $value <= $compare;
    }
}

<?php

namespace tourze\Base\Security;

use ArrayObject;

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
     * 检查数组是否为空
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
        return (bool) preg_match($expression, (string) $value);
    }

    /**
     * 是否满足最短长度要求
     *
     * @param   string  $value  value
     * @param   integer $length minimum length required
     *
     * @return  boolean
     */
    public static function minLength($value, $length)
    {
        return strlen($value) >= $length;
    }

    /**
     * 是否满足最大长度要求
     *
     * @param   string  $value  value
     * @param   integer $length maximum length required
     *
     * @return  boolean
     */
    public static function maxLength($value, $length)
    {
        return strlen($value) <= $length;
    }

    /**
     * Checks that a field is exactly the right length.
     *
     * @param   string        $value  value
     * @param   integer|array $length exact length required, or array of valid lengths
     *
     * @return  boolean
     */
    public static function exactLength($value, $length)
    {
        if (is_array($length))
        {
            foreach ($length as $strlen)
            {
                if (strlen($value) === $strlen)
                {
                    return true;
                }
            }

            return false;
        }

        return strlen($value) === $length;
    }

    /**
     * Checks that a field is exactly the value required.
     *
     * @param   string $value    value
     * @param   string $required required value
     *
     * @return  boolean
     */
    public static function equals($value, $required)
    {
        return ($value === $required);
    }

    /**
     * Check an email address for correct format.
     *
     * @link  http://www.iamcal.com/publish/articles/php/parsing_email/
     * @link  http://www.w3.org/Protocols/rfc822/
     *
     * @param   string  $email  email address
     * @param   boolean $strict strict RFC compatibility
     *
     * @return  boolean
     */
    public static function email($email, $strict = false)
    {
        if (strlen($email) > 254)
        {
            return false;
        }

        if (true === $strict)
        {
            $qText = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            $dText = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            $pair = '\\x5c[\\x00-\\x7f]';

            $domainLiteral = "\\x5b($dText|$pair)*\\x5d";
            $quotedString = "\\x22($qText|$pair)*\\x22";
            $subDomain = "($atom|$domainLiteral)";
            $word = "($atom|$quotedString)";
            $domain = "$subDomain(\\x2e$subDomain)*";
            $localPart = "$word(\\x2e$word)*";

            $expression = "/^$localPart\\x40$domain$/D";
        }
        else
        {
            $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})$/iD';
        }

        return (bool) preg_match($expression, (string) $email);
    }

    /**
     * Validate the domain of an email address by checking if the domain has a
     * valid MX record.
     *
     * @link  http://php.net/checkdnsrr  not added to Windows until PHP 5.3.0
     *
     * @param   string $email email address
     *
     * @return  boolean
     */
    public static function emailDomain($email)
    {
        if ( ! Valid::notEmpty($email))
        {
            return false;
        }

        // 检查MX记录
        return (bool) checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
    }

    /**
     * Validate a URL.
     *
     * @param   string $url URL
     *
     * @return  boolean
     */
    public static function url($url)
    {
        // Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
        if ( ! preg_match(
            '~^

			# scheme
			[-a-z0-9+.]++://

			# username:password (optional)
			(?:
				    [-a-z0-9$_.+!*\'(),;?&=%]++   # username
				(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
				@
			)?

			(?:
				# ip address
				\d{1,3}+(?:\.\d{1,3}+){3}+

				| # or

				# hostname (captured)
				(
					     (?!-)[-a-z0-9]{1,63}+(?<!-)
					(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
				)
			)

			# port (optional)
			(?::\d{1,5}+)?

			# path (optional)
			(?:/.*)?

			$~iDx', $url, $matches)
        )
        {
            return false;
        }

        // We matched an IP address
        if ( ! isset($matches[1]))
        {
            return true;
        }

        // Check maximum length of the whole hostname
        // http://en.wikipedia.org/wiki/Domain_name#cite_note-0
        if (strlen($matches[1]) > 253)
        {
            return false;
        }

        // An extra check for the top level domain
        // It must start with a letter
        $tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');

        return ctype_alpha($tld[0]);
    }

    /**
     * Validate an IP.
     *
     * @param   string  $ip           IP address
     * @param   boolean $allowPrivate allow private IP networks
     *
     * @return  boolean
     */
    public static function ip($ip, $allowPrivate = true)
    {
        // Do not allow reserved addresses
        $flags = FILTER_FLAG_NO_RES_RANGE;

        if (false === $allowPrivate)
        {
            // Do not allow private or reserved addresses
            $flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * 检验输入的数据是否为合适的信用卡号码
     *
     * @param   integer      $number credit card number
     * @param   string|array $type   card type, or an array of card types
     *
     * @return  boolean
     * @uses    Valid::luhn
     */
    public static function creditCard($number, $type = null)
    {
        // Remove all non-digit characters from the number
        if (($number = preg_replace('/\D+/', '', $number)) === '')
        {
            return false;
        }

        if (null == $type)
        {
            // Use the default type
            $type = 'default';
        }
        elseif (is_array($type))
        {
            foreach ($type as $t)
            {
                // Test each type for validity
                if (Valid::creditCard($number, $t))
                {
                    return true;
                }
            }

            return false;
        }

        $cards = self::$creditCardRules;

        // Check card type
        $type = strtolower($type);

        if ( ! isset($cards[$type]))
        {
            return false;
        }

        // Check card number length
        $length = strlen($number);

        // Validate the card length by the card type
        if ( ! in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
        {
            return false;
        }

        // Check card number prefix
        if ( ! preg_match('/^' . $cards[$type]['prefix'] . '/', $number))
        {
            return false;
        }

        // No Luhn check required
        if (false == $cards[$type]['luhn'])
        {
            return true;
        }

        return Valid::luhn($number);
    }

    /**
     * Validate a number against the [Luhn](http://en.wikipedia.org/wiki/Luhn_algorithm)
     * (mod10) formula.
     *
     * @param   string $number number to check
     *
     * @return  boolean
     */
    public static function luhn($number)
    {
        // Force the value to be a string as this method uses string functions.
        // Converting to an integer may pass PHP_INT_MAX and result in an error!
        $number = (string) $number;

        if ( ! ctype_digit($number))
        {
            // Luhn can only be used on numbers!
            return false;
        }

        // Check number length
        $length = strlen($number);

        // Checksum of the card number
        $checksum = 0;

        for ($i = $length - 1; $i >= 0; $i -= 2)
        {
            // Add up every 2nd digit, starting from the right
            // 强制转换为数值
            $checksum += (int) substr($number, $i, 1);
        }

        for ($i = $length - 2; $i >= 0; $i -= 2)
        {
            // Add up every 2nd digit doubled, starting from the right
            $double = substr($number, $i, 1) * 2;
            // Subtract 9 from the double where value is greater than 10
            // 强制转换为数值
            $checksum += (int) (($double >= 10) ? ($double - 9) : $double);
        }

        // If the checksum is a multiple of 10, the number is valid
        return ($checksum % 10 === 0);
    }

    /**
     * Checks if a phone number is valid.
     *
     * @param   string $number phone number to check
     * @param   array  $lengths
     *
     * @return  boolean
     */
    public static function phone($number, $lengths = null)
    {
        if ( ! is_array($lengths))
        {
            $lengths = [
                7,
                10,
                11
            ];
        }

        // Remove all non-digit characters from the number
        $number = preg_replace('/\D+/', '', $number);

        // Check if the number is within range
        return in_array(strlen($number), $lengths);
    }

    /**
     * Tests if a string is a valid date string.
     *
     * @param   string $str date to check
     *
     * @return  boolean
     */
    public static function date($str)
    {
        return (false !== strtotime($str));
    }

    /**
     * Checks whether a string consists of alphabetical characters only.
     *
     * @param   string  $str  input string
     * @param   boolean $utf8 trigger UTF-8 compatibility
     *
     * @return  boolean
     */
    public static function alpha($str, $utf8 = false)
    {
        $str = (string) $str;

        if (true === $utf8)
        {
            return (bool) preg_match('/^\pL++$/uD', $str);
        }
        else
        {
            return ctype_alpha($str);
        }
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
     * @param   string  $str  input string
     * @param   boolean $utf8 trigger UTF-8 compatibility
     *
     * @return  boolean
     */
    public static function digit($str, $utf8 = false)
    {
        if (true === $utf8)
        {
            return (bool) preg_match('/^\pN++$/uD', $str);
        }
        else
        {
            return (is_int($str) && $str >= 0) || ctype_digit($str);
        }
    }

    /**
     * 检查输入字符串是否为有效数字（正负数都可以）
     *
     * @param   string $str input string
     *
     * @return  boolean
     */
    public static function numeric($str)
    {
        list($decimal) = array_values(localeconv());
        return (bool) preg_match('/^-?+(?=.*[0-9])[0-9]*+' . preg_quote($decimal) . '?+[0-9]*+$/D', (string) $str);
    }

    /**
     * Check whether a string is not negative
     *
     * @static
     *
     * @param string $value
     *
     * @return bool
     */
    public static function notNegative($value)
    {
        return Valid::numeric($value) && $value >= 0;
    }

    /**
     * Tests if a number is within a range.
     *
     * @param   string  $number number to check
     * @param   integer $min    minimum value
     * @param   integer $max    maximum value
     * @param   integer $step   increment size
     *
     * @return  boolean
     */
    public static function range($number, $min, $max, $step = null)
    {
        if ($number < $min || $number > $max)
        {
            // NumberHelper is outside of range
            return false;
        }

        if ( ! $step)
        {
            // Default to steps of 1
            $step = 1;
        }

        // Check step requirements
        return (($number - $min) % $step === 0);
    }

    /**
     * Checks if a string is a proper decimal format. Optionally, a specific
     * number of digits can be checked too.
     *
     * @param   string  $str    number to check
     * @param   integer $places number of decimal places
     * @param   integer $digits number of digits
     *
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
     *
     * @return  boolean
     */
    public static function color($str)
    {
        return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
    }

    /**
     * 比较两个值是否相等
     *
     * @param $text1
     * @param $text2
     *
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
     *
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
     *
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
     *
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
     *
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
     *
     * @return  bool
     */
    public static function lte($value, $compare)
    {
        return $value <= $compare;
    }

    /**
     * Check that the given date is not more/less than the provided
     * amount of years. This is useful for checking the age of
     * something, e.g. birthday (must be >= 18 years old)
     *
     * @param   string $date
     * @param   string $operator See php.net/version_compare for possible operators
     * @param   int    $years
     *
     * @return mixed
     */
    public static function age($date, $operator, $years)
    {
        $date = new \DateTime($date);
        $diff = $date->diff(new \DateTime);

        return version_compare($diff->y, $years, $operator);
    }
}

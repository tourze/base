<?php

namespace tourze\Base\Helper;

use RandomLib\Factory as RandomFactory;
use SecurityLib\Strength as RandomStrength;

/**
 * 文本助手类
 *
 * @package    Base
 * @category   Helpers
 * @author     YwiSax
 */
class Text
{

    /**
     * @var  array   number units and text equivalents
     */
    public static $units = [
        1000000000 => 'billion',
        1000000    => 'million',
        1000       => 'thousand',
        100        => 'hundred',
        90         => 'ninety',
        80         => 'eighty',
        70         => 'seventy',
        60         => 'sixty',
        50         => 'fifty',
        40         => 'forty',
        30         => 'thirty',
        20         => 'twenty',
        19         => 'nineteen',
        18         => 'eighteen',
        17         => 'seventeen',
        16         => 'sixteen',
        15         => 'fifteen',
        14         => 'fourteen',
        13         => 'thirteen',
        12         => 'twelve',
        11         => 'eleven',
        10         => 'ten',
        9          => 'nine',
        8          => 'eight',
        7          => 'seven',
        6          => 'six',
        5          => 'five',
        4          => 'four',
        3          => 'three',
        2          => 'two',
        1          => 'one',
    ];

    /**
     * Limits a phrase to a given number of words.
     *     $text = self::limitWords($text);
     *
     * @param   string  $str     phrase to limit words of
     * @param   int $limit   number of words to limit to
     * @param   string  $endChar end character or entity
     *
     * @return  string
     */
    public static function limitWords($str, $limit = 100, $endChar = null)
    {
        $limit = (int) $limit;
        $endChar = (null === $endChar) ? '…' : $endChar;

        if (trim($str) === '')
        {
            return $str;
        }

        if ($limit <= 0)
        {
            return $endChar;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,' . $limit . '}/u', $str, $matches);

        // Only attach the end character if the matched string is shorter
        // than the starting string.
        return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $endChar);
    }

    /**
     * Limits a phrase to a given number of characters.
     *     $text = self::limitChars($text);
     *
     * @param   string  $str           phrase to limit characters of
     * @param   int $limit         number of characters to limit to
     * @param   string  $endChar       end character or entity
     * @param   boolean $preserveWords enable or disable the preservation of words while limiting
     *
     * @return  string
     * @uses    UTF8::strlen
     */
    public static function limitChars($str, $limit = 100, $endChar = null, $preserveWords = false)
    {
        $endChar = (null === $endChar) ? '…' : $endChar;

        $limit = (int) $limit;

        if (trim($str) === '' || strlen($str) <= $limit)
        {
            return $str;
        }

        if ($limit <= 0)
        {
            return $endChar;
        }

        if (false === $preserveWords)
        {
            return rtrim(substr($str, 0, $limit)) . $endChar;
        }

        // Don't preserve words. The limit is considered the top limit.
        // No strings with a length longer than $limit should be returned.
        if ( ! preg_match('/^.{0,' . $limit . '}\s/us', $str, $matches))
        {
            return $endChar;
        }

        return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $endChar);
    }

    /**
     * 返回字符串长度
     *
     * @param  string $value
     * @return int
     */
    public static function length($value)
    {
        return strlen($value);
    }

    /**
     * Alternates between two or more strings.
     *
     *     echo self::alternate('one', 'two'); // "one"
     *     echo self::alternate('one', 'two'); // "two"
     *     echo self::alternate('one', 'two'); // "one"
     *
     * Note that using multiple iterations of different strings may produce unexpected results.
     *
     * @return string
     * @internal param string $str strings to alternate between
     */
    public static function alternate()
    {
        static $i;

        if (func_num_args() === 0)
        {
            $i = 0;

            return '';
        }

        $args = func_get_args();

        return $args[($i++ % count($args))];
    }

    /**
     * 生成随机字符串
     *
     * @param int $length
     * @param string  $pool
     * @return string
     */
    public static function random($length = 20, $pool = '')
    {
        $factory = new RandomFactory;
        $generator = $factory->getGenerator(new RandomStrength(RandomStrength::MEDIUM));

        return $generator->generateString($length, $pool);
    }

    /**
     * Reduces multiple slashes in a string to single slashes.
     *
     *     $str = self::reduceSlashes('foo//bar/baz'); // "foo/bar/baz"
     *
     * @param   string $str string to reduce slashes of
     * @return  string
     */
    public static function reduceSlashes($str)
    {
        return preg_replace('#(?<!:)//+#', '/', $str);
    }

    /**
     * 字符过滤
     *
     *     // 返回 "What the #####, man!"
     *     echo self::censor('What the fuck, man!', [
     *         'fuck' => '#####',
     *     ]);
     *
     * @param   string  $str                 phrase to replace words in
     * @param   array   $badWords            words to replace
     * @param   string  $replacement         replacement string
     * @param   boolean $replacePartialWords replace words across word boundaries (space, period, etc)
     * @return  string
     */
    public static function censor($str, $badWords, $replacement = '#', $replacePartialWords = true)
    {
        foreach ((array) $badWords as $key => $badWord)
        {
            $badWords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badWord));
        }

        $regex = '(' . implode('|', $badWords) . ')';

        if (false === $replacePartialWords)
        {
            // Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
            $regex = '(?<=\b|\s|^)' . $regex . '(?=\b|\s|$)';
        }

        $regex = '!' . $regex . '!ui';

        if (strlen($replacement) == 1)
        {
            $regex .= 'e';
            return preg_replace($regex, 'str_repeat($replacement, strlen(\'$1\'))', $str);
        }

        return preg_replace($regex, $replacement, $str);
    }

    /**
     * 返回指定字符串的相似部分
     *
     *     $match = self::similar('fred', 'fran', 'free'); // "fr"
     *
     * @return  string
     */
    public static function similar()
    {
        $words = func_get_args();
        // First word is the word to match against
        $word = current($words);

        for ($i = 0, $max = strlen($word); $i < $max; ++$i)
        {
            foreach ($words as $w)
            {
                // Once a difference is found, break out of the loops
                if ( ! isset($w[$i]) || $w[$i] !== $word[$i])
                {
                    break 2;
                }
            }
        }

        // Return the similar text
        return substr($word, 0, $i);
    }

    /**
     * 返回字节数的可读形式
     *
     *     echo self::bytes(filesize($file));
     *
     * @param   int $bytes     字节数
     * @param   string  $forceUnit a definitive unit
     * @param   string  $format    the return string format
     * @param   boolean $si        whether to use SI prefixes or IEC
     *
     * @return  string
     */
    public static function bytes($bytes, $forceUnit = null, $format = null, $si = true)
    {
        // Format string
        $format = (null === $format) ? '%01.2f %s' : (string) $format;

        // IEC prefixes (binary)
        if (false == $si || false !== strpos($forceUnit, 'i'))
        {
            $units = [
                'B',
                'KiB',
                'MiB',
                'GiB',
                'TiB',
                'PiB'
            ];
            $mod = 1024;
        }
        // SI prefixes (decimal)
        else
        {
            $units = [
                'B',
                'kB',
                'MB',
                'GB',
                'TB',
                'PB'
            ];
            $mod = 1000;
        }

        // Determine unit to use
        if (false === ($power = array_search((string) $forceUnit, $units)))
        {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }

    /**
     * 返回指定数值的文本描述
     *
     *     // 返回：one thousand and twenty-four
     *     echo Text::number(1024);
     *     // 返回：five million, six hundred and thirty-two
     *     echo Text::number(5000632);
     *
     * @param   int $number number to format
     * @return  string
     */
    public static function number($number)
    {
        // The number must always be an integer
        $number = (int) $number;

        $text = [];

        // Last matched unit within the loop
        $lastUnit = null;

        // The last matched item within the loop
        $lastItem = '';

        foreach (self::$units as $unit => $name)
        {
            if ($number / $unit >= 1)
            {
                // $value = the number of times the number is divisible by unit
                $number -= $unit * ($value = (int) floor($number / $unit));
                $item = '';

                if ($unit < 100)
                {
                    if ($lastUnit < 100 && $lastUnit >= 20)
                    {
                        $lastItem .= '-' . $name;
                    }
                    else
                    {
                        $item = $name;
                    }
                }
                else
                {
                    $item = self::number($value) . ' ' . $name;
                }

                // In the situation that we need to make a composite number (i.e. twenty-three)
                // then we need to modify the previous entry
                if (empty($item))
                {
                    array_pop($text);

                    $item = $lastItem;
                }

                $lastItem = $text[] = $item;
                $lastUnit = $unit;
            }
        }

        if (count($text) > 1)
        {
            $and = array_pop($text);
        }

        $text = implode(', ', $text);

        if (isset($and))
        {
            $text .= ' and ' . $and;
        }

        return $text;
    }

    /**
     * 创建驼峰命名
     *
     *     $str = self::camelize('mother cat');     // "motherCat"
     *     $str = self::camelize('kittens in bed'); // "kittensInBed"
     *
     * @param   string $str 要解析的字符串
     * @param   string $dot 删除的字符串，如空格
     * @return  string
     */
    public static function camelize($str, $dot = ' ')
    {
        $str = str_replace($dot, '', ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', $str)));

        return str_replace(' ', '', $str);
    }

    /**
     * 反驼峰命名
     *
     *     $str = self::decamelize('houseCat');    // "house cat"
     *     $str = self::decamelize('kingAllyCat'); // "king ally cat"
     *
     * @param   string $str phrase to camelize
     * @param   string $sep word separator
     * @return  string
     */
    public static function decamelize($str, $sep = ' ')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1' . $sep . '$2', trim($str)));
    }

    /**
     * 下划线风格
     *
     *     $str = self::underscore('five cats'); // "five_cats";
     *
     * @param   string $str 处理字符串
     * @return  string
     */
    public static function underscore($str)
    {
        return preg_replace('/\s+/', '_', trim($str));
    }

    /**
     * 中划线风格
     *
     *     $str = self::humanize('kittens-are-cats'); // "kittens are cats"
     *     $str = self::humanize('dogs_as_well');     // "dogs as well"
     *
     * @param   string $str phrase to make human-readable
     * @return  string
     */
    public static function humanize($str)
    {
        return preg_replace('/[_-]+/', ' ', trim($str));
    }

    /**
     * 检测字符串是否以指定字符串开头
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function startWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if (strpos($haystack, $needle) === 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * 检测字符串是否以指定字符串结尾
     *
     * @param string       $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function endWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ($needle == substr($haystack, strlen($haystack) - strlen($needle)))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains a given sub-string.
     *
     * @param  string       $haystack
     * @param  string|array $needle
     *
     * @return bool
     */
    public static function contains($haystack, $needle)
    {
        foreach ((array) $needle as $n)
        {
            if (false !== strpos($haystack, $n))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * parses a string of params into an array, and changes numbers to ints
     *
     *    params('depth=2,something=test')
     *
     *    becomes
     *
     *    array(2) (
     *       "depth" => int 2
     *       "something" => string(4) "test"
     *    )
     *
     * @param  string $var the params to parse
     * @return array   the resulting array
     */
    public static function params($var)
    {
        $var = explode(',', $var);
        $new = [];
        foreach ($var AS $i)
        {
            $i = explode('=', trim($i));
            $new[$i[0]] = Arr::get($i, 1, null);

            if (is_numeric($new[$i[0]]))
            {
                $new[$i[0]] = (int) $new[$i[0]];
            }
        }

        return $new;
    }
}

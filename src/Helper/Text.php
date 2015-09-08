<?php

namespace tourze\Base\Helper;

use Doctrine\Common\Inflector\Inflector;
use phpSec\Crypt\Rand;

/**
 * 文本助手类
 *
 * @package tourze\Base\Helper
 */
class Text extends HelperBase implements HelperInterface
{

    /**
     * @var array 数字单元的描述
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
     * 限制单词长度
     *
     *     $text = Text::limitWords($text);
     *
     * @param  string $str     要检查的字符
     * @param  int    $limit   限制长度
     * @param  string $endChar 截断后使用该字符来代替
     * @return string
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

        return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $endChar);
    }

    /**
     * 限制字符长度
     *
     *     $text = Text::limitChars($text);
     *
     * @param  string $str           要限制的字符
     * @param  int    $limit         限制长度
     * @param  string $endChar       结尾字符
     * @param  bool   $preserveWords 激活或禁用字符保护
     * @return string
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
     * 两个字符串之间交替返回
     *
     *     echo Text::alternate('one', 'two'); // "one"
     *     echo Text::alternate('one', 'two'); // "two"
     *     echo Text::alternate('one', 'two'); // "one"
     *
     * @return string
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
     * @param int    $length
     * @param string $pool
     * @return string
     */
    public static function random($length = 20, $pool = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890')
    {
        $obj = new Rand;
        return $obj->str($length, $pool);
    }

    /**
     * 删除字符串中多余的斜杆
     *
     *     $str = Text::reduceSlashes('foo//bar/baz'); // "foo/bar/baz"
     *
     * @param  string $str 要处理的字符串
     * @return string
     */
    public static function reduceSlashes($str)
    {
        return preg_replace('#(?<!:)//+#', '/', $str);
    }

    /**
     * 字符过滤
     *
     *     // 返回 "What the #####, man!"
     *     echo Text::censor('What the fuck, man!', [
     *         'fuck' => '#####',
     *     ]);
     *
     * @param  string $str         要过滤的字符串
     * @param  array  $badwordList 敏感词汇列表
     * @param  string $replacement 默认替换成这个字符
     * @return string
     */
    public static function censor($str, $badwordList, $replacement = '#')
    {
        // 如果替换列表不是关联数组，那么就手动处理一次
        if ( ! Arr::isAssoc($badwordList))
        {
            $temp = [];
            foreach ($badwordList as $badword)
            {
                $temp[$badword] = str_repeat($replacement, strlen($badword));
            }
            $badwordList = $temp;
        }

        foreach ($badwordList as $badword => $replace)
        {
            $str = str_replace($badword, $replace, $str);
        }

        return $str;
    }

    /**
     * 返回指定字符串的相似部分
     *
     *     $match = Text::similar('fred', 'fran', 'free'); // "fr"
     *
     * @return  string
     */
    public static function similar()
    {
        $words = func_get_args();
        $word = current($words);

        for ($i = 0, $max = strlen($word); $i < $max; ++$i)
        {
            foreach ($words as $w)
            {
                // 如果找到差异了，立即退出
                if ( ! isset($w[$i]) || $w[$i] !== $word[$i])
                {
                    break 2;
                }
            }
        }

        return substr($word, 0, $i);
    }

    /**
     * 返回字节数的可读形式
     *
     *     echo Text::bytes(filesize($file));
     *
     * @param  int    $bytes  字节数
     * @param  string $unit   单元
     * @param  string $format 返回字符串的格式
     * @param  bool   $si     是否使用SI前缀或IEC
     * @return string
     */
    public static function bytes($bytes, $unit = null, $format = null, $si = true)
    {
        $format = (null === $format) ? '%01.2f %s' : (string) $format;

        // IEC前缀（二进制）
        if (false == $si || false !== strpos($unit, 'i'))
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
        // SI前缀（十进制）
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

        if (false === ($power = array_search((string) $unit, $units)))
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
     *
     *     // 返回：five million, six hundred and thirty-two
     *     echo Text::number(5000632);
     *
     * @param  int $number 要格式化的数值
     * @return string
     */
    public static function number($number)
    {
        $number = (int) $number;
        $text = [];
        $lastUnit = null;
        $lastItem = '';

        foreach (self::$units as $unit => $name)
        {
            if ($number / $unit >= 1)
            {
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
     *     $str = Text::camelize('mother cat');     // "motherCat"
     *     $str = Text::camelize('kittens in bed'); // "kittensInBed"
     *
     * @param  string $str 要解析的字符串
     * @return string
     */
    public static function camelize($str)
    {
        return Inflector::camelize($str);
    }

    /**
     * 反驼峰命名
     *
     *     $str = Text::decamelize('houseCat');    // "house cat"
     *     $str = Text::decamelize('kingAllyCat'); // "king ally cat"
     *
     * @param  string $str 驼峰风格字符串
     * @param  string $sep 分隔符
     * @return string
     */
    public static function decamelize($str, $sep = ' ')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1' . $sep . '$2', trim($str)));
    }

    /**
     * 下划线风格
     *
     *     $str = Text::underscore('five cats'); // "five_cats";
     *
     * @param  string $str 处理字符串
     * @return string
     */
    public static function underscore($str)
    {
        return preg_replace('/\s+/', '_', trim($str));
    }

    /**
     * 中划线风格
     *
     *     $str = Text::humanize('kittens-are-cats'); // "kittens are cats"
     *     $str = Text::humanize('dogs_as_well');     // "dogs as well"
     *
     * @param  string $str 要处理的字符串
     * @return string
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
     * 检查指定字符串是否包含另外一个字符串
     *
     * @param  string       $haystack
     * @param  string|array $needle
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
     * 解析参数字符串为数组
     *
     *    params('depth=2,something=test')
     *
     *    转换为
     *
     *    array(2) (
     *       "depth" => int 2
     *       "something" => string(4) "test"
     *    )
     *
     * @param  string $var 要解析的字符串
     * @return array
     */
    public static function params($var)
    {
        $var = explode(',', $var);
        $new = [];
        foreach ($var AS $i)
        {
            $i = explode('=', trim($i));
            $new[$i[0]] = Arr::get($i, 1, null);

            if (ctype_digit($new[$i[0]]))
            {
                $new[$i[0]] = (int) $new[$i[0]];
            }
        }

        return $new;
    }
}

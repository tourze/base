<?php

namespace tourze\Base\Helper;

use DateTime;
use DateTimeZone;

/**
 * 日期助手类
 *
 * @package tourze\Base\Helper
 */
class Date extends HelperBase implements HelperInterface
{

    // 基于秒数的各时间单位
    const YEAR   = 31556926;
    const MONTH  = 2629744;
    const WEEK   = 604800;
    const DAY    = 86400;
    const HOUR   = 3600;
    const MINUTE = 60;

    // Date::months()中可用的格式
    const MONTHS_LONG = '%B';

    const MONTHS_SHORT = '%b';

    const SPAN_YEARS = 'years';

    const SPAN_MONTHS = 'months';

    const SPAN_WEEKS = 'weeks';

    const SPAN_DAYS = 'days';

    const SPAN_HOURS = 'hours';

    const SPAN_MINUTES = 'minutes';

    const SPAN_SECONDS = 'seconds';

    /**
     * 默认时间戳格式
     *
     * @var string
     */
    public static $timestampFormat = 'Y-m-d H:i:s';

    /**
     * 默认时区
     *
     * @link http://uk2.php.net/manual/en/timezones.php
     * @var string
     */
    public static $timezone;

    /**
     * 返回两个时区之前的偏移值
     *
     *     $seconds = Date::offset('America/Chicago', 'GMT');
     *
     * @see    http://php.net/timezones
     * @param  string $remote 要查找偏移的时区
     * @param  string $local  要对比的基础时区
     * @param  mixed  $now    UNIX时间戳或秒数时间的字符串
     * @return int
     */
    public static function offset($remote, $local = null, $now = null)
    {
        if ($local === null)
        {
            $local = date_default_timezone_get();
        }

        if (is_int($now))
        {
            $now = date(DateTime::RFC2822, $now);
        }

        $zoneRemote = new DateTimeZone($remote);
        $zoneLocal = new DateTimeZone($local);

        $timeRemote = new DateTime($now, $zoneRemote);
        $timeLocal = new DateTime($now, $zoneLocal);

        $offset = $zoneRemote->getOffset($timeRemote) - $zoneLocal->getOffset($timeLocal);

        return $offset;
    }

    /**
     * 将一个非24小时制的时间转换为24小时制
     *
     *     $hour = Date::adjust(3, 'pm'); // 15
     *
     * @param  int    $hour   小时
     * @param  string $format AM/PM
     * @return string
     */
    public static function adjust($hour, $format)
    {
        $hour = (int) $hour;
        $format = strtolower($format);

        switch ($format)
        {
            case 'am':
                if ($hour == 12)
                {
                    $hour = 0;
                }
                break;
            case 'pm':
                if ($hour < 12)
                {
                    $hour += 12;
                }
                break;
        }

        return sprintf('%02d', $hour);
    }

    /**
     * 返回指定年份和月份的日期列表
     *
     *     Date::days(4, 2010); // 1, 2, 3, ..., 28, 29, 30
     *
     * @param  int $month 月份
     * @param  int $year  年份，默认为今年
     * @return array
     */
    public static function days($month, $year = 0)
    {
        static $months;

        if ( ! $year)
        {
            $year = date('Y');
        }

        $month = (int) $month;
        $year = (int) $year;

        if ( ! isset($months[$year][$month]))
        {
            $months[$year][$month] = [];

            // 直接使用内置方法来查找这个月份有多少天，这样就不用自己去做判断啦
            $total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

            for ($i = 1; $i < $total; $i++)
            {
                $months[$year][$month][$i] = (string) $i;
            }
        }

        return $months[$year][$month];
    }

    /**
     * 返回一年中的12个月份
     *
     *     Date::months();
     *     // array(1 => 1, 2 => 2, 3 => 3, ..., 12 => 12)
     *
     * 可以使用Date::MONTHS_LONG来使其返回带月份的格式
     *
     *     Date::months(Date::MONTHS_LONG);
     *     // array(1 => 'January', 2 => 'February', ..., 12 => 'December')
     *
     * Date::MONTHS_SHORT返回月份的短格式
     *
     *     Date::months(Date::MONTHS_SHORT);
     *     // array(1 => 'Jan', 2 => 'Feb', ..., 12 => 'Dec')
     *
     * @param  string $format 月份格式
     * @return array
     */
    public static function months($format = null)
    {
        $months = [];

        if ($format === Date::MONTHS_LONG || $format === Date::MONTHS_SHORT)
        {
            for ($i = 1; $i <= 12; ++$i)
            {
                $months[$i] = strftime($format, mktime(0, 0, 0, $i, 1));
            }
        }
        else
        {
            $array = Arr::range(1, 12);
            foreach ($array as $i)
            {
                $months[$i] = $i;
            }
        }

        return $months;
    }

    /**
     * 返回一个年份列表
     *
     *     $years = Date::years(2000, 2010); // 2000, 2001, ..., 2009, 2010
     *
     * @param  int $start 开始年份（默认为当前年份-5）
     * @param  int $end   结束年份（默认为当前年份+5）
     * @return array
     */
    public static function years($start = 0, $end = 0)
    {
        $start = ($start === 0) ? (date('Y') - 5) : (int) $start;
        $end = ($end === 0) ? (date('Y') + 5) : (int) $end;

        $years = [];

        for ($i = $start; $i <= $end; $i++)
        {
            $years[$i] = (string) $i;
        }

        return $years;
    }

    /**
     * 返回两个时间之间差别的可读版本
     *
     *     $span = Date::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     *     $span = Date::span(60, 182, 'minutes'); // 2
     *
     * @param  int    $remote 要查找的时间
     * @param  int    $local  要比较的时间
     * @param  string $output 格式化字符串
     * @return string   当只需要返回一个单一输出时when only a single output is requested
     * @return array    所有关联信息
     */
    public static function span($remote, $local = null, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        // Normalize output
        $output = trim(strtolower((string) $output));

        if ( ! $output)
        {
            // Invalid output
            return false;
        }

        // Array with the output formats
        $output = preg_split('/[^a-z]+/', $output);

        // Convert the list of outputs to an associative array
        $output = array_combine($output, array_fill(0, count($output), 0));

        // Make the output values into keys
        extract(array_flip($output), EXTR_SKIP);

        if ($local === null)
        {
            // Calculate the span from the current time
            $local = time();
        }

        $span = abs($remote - $local);

        if (isset($output['years']))
        {
            $span -= Date::YEAR * ($output['years'] = (int) floor($span / Date::YEAR));
        }

        if (isset($output['months']))
        {
            $span -= Date::MONTH * ($output['months'] = (int) floor($span / Date::MONTH));
        }

        if (isset($output['weeks']))
        {
            $span -= Date::WEEK * ($output['weeks'] = (int) floor($span / Date::WEEK));
        }

        if (isset($output['days']))
        {
            $span -= Date::DAY * ($output['days'] = (int) floor($span / Date::DAY));
        }

        if (isset($output['hours']))
        {
            $span -= Date::HOUR * ($output['hours'] = (int) floor($span / Date::HOUR));
        }

        if (isset($output['minutes']))
        {
            $span -= Date::MINUTE * ($output['minutes'] = (int) floor($span / Date::MINUTE));
        }

        // Seconds ago, 1
        if (isset($output['seconds']))
        {
            $output['seconds'] = $span;
        }

        if (count($output) === 1)
        {
            // Only a single output was requested, return it
            return array_pop($output);
        }

        // Return array
        return $output;
    }

    /**
     * 返回两个时间之间差异的文字描述
     *
     *     $span = Date::fuzzySpan(time() - 10); // "moments ago"
     *     $span = Date::fuzzySpan(time() + 20); // "in moments"
     *
     * @param  int $timestamp        时间戳
     * @param  int $compareTimestamp 对比的时间戳，默认是time()
     * @return string
     */
    public static function fuzzySpan($timestamp, $compareTimestamp = null)
    {
        $compareTimestamp = ($compareTimestamp === null) ? time() : (int) $compareTimestamp;

        // Determine the difference in seconds
        $offset = abs($compareTimestamp - $timestamp);

        if ($offset <= Date::MINUTE)
        {
            $span = 'moments';
        }
        elseif ($offset < (Date::MINUTE * 20))
        {
            $span = 'a few minutes';
        }
        elseif ($offset < Date::HOUR)
        {
            $span = 'less than an hour';
        }
        elseif ($offset < (Date::HOUR * 4))
        {
            $span = 'a couple of hours';
        }
        elseif ($offset < Date::DAY)
        {
            $span = 'less than a day';
        }
        elseif ($offset < (Date::DAY * 2))
        {
            $span = 'about a day';
        }
        elseif ($offset < (Date::DAY * 4))
        {
            $span = 'a couple of days';
        }
        elseif ($offset < Date::WEEK)
        {
            $span = 'less than a week';
        }
        elseif ($offset < (Date::WEEK * 2))
        {
            $span = 'about a week';
        }
        elseif ($offset < Date::MONTH)
        {
            $span = 'less than a month';
        }
        elseif ($offset < (Date::MONTH * 2))
        {
            $span = 'about a month';
        }
        elseif ($offset < (Date::MONTH * 4))
        {
            $span = 'a couple of months';
        }
        elseif ($offset < Date::YEAR)
        {
            $span = 'less than a year';
        }
        elseif ($offset < (Date::YEAR * 2))
        {
            $span = 'about a year';
        }
        elseif ($offset < (Date::YEAR * 4))
        {
            $span = 'a couple of years';
        }
        elseif ($offset < (Date::YEAR * 8))
        {
            $span = 'a few years';
        }
        elseif ($offset < (Date::YEAR * 12))
        {
            $span = 'about a decade';
        }
        elseif ($offset < (Date::YEAR * 24))
        {
            $span = 'a couple of decades';
        }
        elseif ($offset < (Date::YEAR * 64))
        {
            $span = 'several decades';
        }
        else
        {
            $span = 'a long time';
        }

        if ($timestamp <= $compareTimestamp)
        {
            // 过去
            return $span . ' ago';
        }
        else
        {
            // 未来
            return 'in ' . $span;
        }
    }

    /**
     * 转换UNIX时间戳为DOS时间戳格式
     *
     *     $dos = Date::unix2dos($unix);
     *
     * @param  int $timestamp UNIX时间戳
     * @return int
     */
    public static function unix2dos($timestamp = 0)
    {
        $timestamp = ! $timestamp ? getdate() : getdate($timestamp);

        if ($timestamp['year'] < 1980)
        {
            return (1 << 21 | 1 << 16);
        }

        $timestamp['year'] -= 1980;

        return ($timestamp['year'] << 25 | $timestamp['mon'] << 21 |
            $timestamp['mday'] << 16 | $timestamp['hours'] << 11 |
            $timestamp['minutes'] << 5 | $timestamp['seconds'] >> 1);
    }

    /**
     * 转换DOS时间戳格式为UNIX格式
     *
     *     $unix = Date::dos2unix($dos);
     *
     * @param  int $timestamp DOS时间戳
     * @return int
     */
    public static function dos2unix($timestamp)
    {
        $sec = 2 * ($timestamp & 0x1f);
        $min = ($timestamp >> 5) & 0x3f;
        $hrs = ($timestamp >> 11) & 0x1f;
        $day = ($timestamp >> 16) & 0x1f;
        $mon = ($timestamp >> 21) & 0x0f;
        $year = ($timestamp >> 25) & 0x7f;

        return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
    }

    /**
     * 传入时间描述，返回指定格式的时间戳
     *
     *     $time = Date::formatTime('5 minutes ago');
     *
     * @link   http://www.php.net/manual/datetime.construct
     * @param  string $datetimeStr     datetime字符串
     * @param  string $timestampFormat timestamp格式
     * @param  string $timezone        时区分隔符
     * @return string
     */
    public static function formatTime($datetimeStr = 'now', $timestampFormat = null, $timezone = null)
    {
        $timestampFormat = ($timestampFormat == null) ? Date::$timestampFormat : $timestampFormat;
        $timezone = ($timezone === null) ? Date::$timezone : $timezone;

        $tz = new DateTimeZone($timezone ? $timezone : date_default_timezone_get());
        $time = new DateTime($datetimeStr, $tz);

        if ($time->getTimeZone()->getName() !== $tz->getName())
        {
            $time->setTimeZone($tz);
        }

        return $time->format($timestampFormat);
    }

    /**
     * 多少分钟前
     *
     * @param  int $date 时间戳
     * @return string    字符串
     */
    public static function timeAgo($date)
    {
        $timeSince = false;
        if ( ! empty($date))
        {
            $ago = date('U') - $date;
            $periods = [__('second'), __('minute'), __('hour'), __('day'), __('week'), __('month'), __('year'), __('ten year')];
            $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];
            for ($j = 0; $ago >= $lengths[$j]; $j++)
            {
                $ago /= $lengths[$j];
            }
            $ago = round($ago);

            if ($ago != 1)
            {
                $periods[$j] .= __('s');
            }
            $timeSince = $ago . ' ' . $periods[$j] . __(' ago');
        }

        return $timeSince;
    }

}

<?php

namespace tourze\Base;

use ReflectionFunction;
use ReflectionMethod;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * 调试类的实现
 *
 * @package tourze\Base
 */
class Debug
{

    /**
     * @var bool 是否激活了内置的调试和错误处理方法
     */
    public static $enabled = false;

    /**
     * @var  array  需要显示出来的错误信息级别
     */
    public static $shutdownErrors = [
        E_PARSE,
        E_ERROR,
        E_USER_ERROR
    ];

    /**
     * 激活调试器
     */
    public static function enable()
    {
        if (self::$enabled)
        {
            return;
        }

        $whoops = new Run;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->register();

        self::$enabled = true;
    }

    /**
     * 返回变量的打印html
     *
     *     // 可以打印多个变量
     *     echo self::vars($foo, $bar, $baz);
     *
     * @return string
     */
    public static function vars()
    {
        if (func_num_args() === 0)
        {
            return null;
        }

        $variables = func_get_args();
        $output = [];
        foreach ($variables as $var)
        {
            $output[] = self::_dump($var, 1024);
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * 返回单个变量的HTML格式
     *
     * @param   mixed   $value          variable to dump
     * @param   integer $length         maximum length of strings
     * @param   integer $levelRecursion recursion limit
     *
     * @return  string
     */
    public static function dump($value, $length = 128, $levelRecursion = 10)
    {
        return self::_dump($value, $length, $levelRecursion);
    }

    /**
     * Helper for self::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed   $var    variable to dump
     * @param   integer $length maximum length of strings
     * @param   integer $limit  recursion limit
     * @param   integer $level  current recursion level (internal usage only!)
     *
     * @return  string
     */
    protected static function _dump(& $var, $length = 128, $limit = 10, $level = 0)
    {
        if (null === $var)
        {
            return '<small>null</small>';
        }
        elseif (is_bool($var))
        {
            return '<small>bool</small> ' . ($var ? 'true' : 'false');
        }
        elseif (is_float($var))
        {
            return '<small>float</small> ' . $var;
        }
        elseif (is_resource($var))
        {
            if (($type = get_resource_type($var)) === 'stream' && $meta = stream_get_meta_data($var))
            {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri']))
                {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local'))
                    {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file))
                        {
                            $file = self::path($file);
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, Base::$charset);
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif (is_string($var))
        {
            if (strlen($var) > $length)
            {
                // Encode the truncated string
                $str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, Base::$charset) . '&nbsp;&hellip;';
            }
            else
            {
                // Encode the string
                $str = htmlspecialchars($var, ENT_NOQUOTES, Base::$charset);
            }

            return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }
        elseif (is_array($var))
        {
            $output = [];

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            static $marker;

            if (null === $marker)
            {
                // Make a unique marker
                $marker = uniqid("\x00");
            }

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($var[$marker]))
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif ($level < $limit)
            {
                $output[] = "<span>(";

                $var[$marker] = true;
                foreach ($var as $key => & $val)
                {
                    if ($key === $marker)
                    {
                        continue;
                    }
                    if ( ! is_int($key))
                    {
                        $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, Base::$charset) . '"';
                    }

                    $output[] = "$space$s$key => " . self::_dump($val, $length, $limit, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            }
            else
            {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }
        elseif (is_object($var))
        {
            // Copy the object as an array
            $array = (array) $var;

            $output = [];

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = [];

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($objects[$hash]))
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif ($level < $limit)
            {
                $output[] = "<code>{";

                $objects[$hash] = true;
                foreach ($array as $key => & $val)
                {
                    if ($key[0] === "\x00")
                    {
                        // Determine if the access is protected or protected
                        $access = '<small>' . (($key[1] === '*') ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . self::_dump($val, $length, $limit, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            }
            else
            {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }

        return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, true), ENT_NOQUOTES, Base::$charset);
    }

    /**
     * Removes application, system, modpath, or docroot from a filename,
     * replacing them with the plain text equivalents. Useful for debugging
     * when you want to display a shorter path.
     *     // Displays SYSPATH/classes/Base.php
     *     echo self::path(Base::findFile('classes', 'Base'));
     *
     * @param   string $file path to debug
     *
     * @return  string
     */
    public static function path($file)
    {
        if (strpos($file, APPPATH) === 0)
        {
            $file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
        }
        elseif (strpos($file, SYSPATH) === 0)
        {
            $file = 'SYSPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSPATH));
        }
        elseif (strpos($file, DOCROOT) === 0)
        {
            $file = 'DOCROOT' . DIRECTORY_SEPARATOR . substr($file, strlen(DOCROOT));
        }

        return $file;
    }

    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *     // Highlights the current line of the current file
     *     echo self::source(__FILE__, __LINE__);
     *
     * @param   string  $file        file to open
     * @param   integer $lineNumber line number to highlight
     * @param   integer $padding     number of padding lines
     *
     * @return  string   source of file
     * @return  false    file is unreadable
     */
    public static function source($file, $lineNumber, $padding = 5)
    {
        if ( ! $file || ! is_readable($file))
        {
            // Continuing will cause errors
            return false;
        }

        // Open the file and set the line position
        $file = fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = [
            'start' => $lineNumber - $padding,
            'end'   => $lineNumber + $padding
        ];

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen($range['end']) . 'd';

        $source = '';
        while (false !== ($row = fgets($file)))
        {
            // Increment the line number
            if (++$line > $range['end'])
            {
                break;
            }

            if ($line >= $range['start'])
            {
                // Make the row safe for output
                $row = htmlspecialchars($row, ENT_NOQUOTES, Base::$charset);

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;

                if ($line === $lineNumber)
                {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose($file);

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }

    /**
     * Returns an array of HTML strings that represent each step in the backtrace.
     *     // Displays the entire current backtrace
     *     echo implode('<br/>', self::trace());
     *
     * @param   array $trace
     *
     * @return  array
     */
    public static function trace(array $trace = null)
    {
        if (null === $trace)
        {
            // Start a new trace
            $trace = debug_backtrace();
        }

        // Non-standard function calls
        $statements = [
            'include',
            'include_once',
            'require',
            'require_once'
        ];

        $output = [];
        foreach ($trace as $step)
        {
            if ( ! isset($step['function']))
            {
                // Invalid trace step
                continue;
            }

            if (isset($step['file']) && isset($step['line']))
            {
                // Include the source of this step
                $source = self::source($step['file'], $step['line']);
            }

            if (isset($step['file']))
            {
                $file = $step['file'];

                if (isset($step['line']))
                {
                    $line = $step['line'];
                }
            }

            // function()
            $function = $step['function'];

            if (in_array($step['function'], $statements))
            {
                if (empty($step['args']))
                {
                    // No arguments
                    $args = [];
                }
                else
                {
                    // Sanitize the file path
                    $args = [$step['args'][0]];
                }
            }
            elseif (isset($step['args']))
            {
                if ( ! function_exists($step['function']) || false !== strpos($step['function'], '{closure}'))
                {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = null;
                }
                else
                {
                    if (isset($step['class']))
                    {
                        if (method_exists($step['class'], $step['function']))
                        {
                            $reflection = new ReflectionMethod($step['class'], $step['function']);
                        }
                        else
                        {
                            $reflection = new ReflectionMethod($step['class'], '__call');
                        }
                    }
                    else
                    {
                        $reflection = new ReflectionFunction($step['function']);
                    }

                    // Get the function parameters
                    $params = $reflection->getParameters();
                }

                $args = [];

                foreach ($step['args'] as $i => $arg)
                {
                    if (isset($params[$i]))
                    {
                        // Assign the argument by the parameter name
                        $args[$params[$i]->name] = $arg;
                    }
                    else
                    {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }

            if (isset($step['class']))
            {
                // Class->method() or Class::method()
                $function = $step['class'] . $step['type'] . $step['function'];
            }

            $output[] = [
                'function' => $function,
                'args'     => isset($args) ? $args : null,
                'file'     => isset($file) ? $file : null,
                'line'     => isset($line) ? $line : null,
                'source'   => isset($source) ? $source : null,
            ];

            unset($function, $args, $file, $line, $source);
        }

        return $output;
    }
}

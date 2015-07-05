<?php

namespace tourze\Base\Helper;

use ArrayObject;
use tourze\Base\Exception\InvalidArgumentException;
use Traversable;

/**
 * 数组助手类
 *
 * @package    Base
 * @category   Helpers
 * @author     YwiSax
 */
class Arr
{

    /**
     * @var  string  default delimiter for path()
     */
    public static $delimiter = '.';

    /**
     * 指定数组是否为一个关联数组
     *
     *     // true
     *     ArrayHelper::isAssoc(['username' => 'john.doe']);
     *     // false
     *     ArrayHelper::isAssoc('foo', 'bar');
     *
     * @param   array $array array to check
     *
     * @return  boolean
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * 检查参数是否为一个数组或数组对象
     *
     *     // true
     *     ArrayHelper::isArray([]);
     *     ArrayHelper::isArray(new ArrayObject);
     *
     *     // false
     *     ArrayHelper::isArray(false);
     *     ArrayHelper::isArray('not an array!');
     *     ArrayHelper::isArray(Db::instance());
     *
     * @param   mixed $value value to check
     *
     * @return  boolean
     */
    public static function isArray($value)
    {
        if (is_array($value))
        {
            return true;
        }
        else
        {
            return (is_object($value) && $value instanceof Traversable);
        }
    }

    /**
     * Gets a value from an array using a dot separated path.
     *     // Get the value of $array['foo']['bar']
     *     $value = self::path($array, 'foo.bar');
     * Using a wildcard "*" will search intermediate arrays and return an array.
     *     // Get the values of "color" in theme
     *     $colors = self::path($array, 'theme.*.color');
     *     // Using an array of keys
     *     $colors = self::path($array, ['theme', '*', 'color']);
     *
     * @param   array  $array     array to search
     * @param   mixed  $path      key path string (delimiter separated) or array of keys
     * @param   mixed  $default   default value if the path is not set
     * @param   string $delimiter key path delimiter
     *
     * @return  mixed
     */
    public static function path($array, $path, $default = null, $delimiter = null)
    {
        if ( ! self::isArray($array))
        {
            // This is not an array!
            return $default;
        }

        if (is_array($path))
        {
            // The path has already been separated into keys
            $keys = $path;
        }
        else
        {
            if (array_key_exists($path, $array))
            {
                // No need to do extra processing
                return $array[$path];
            }

            if (null === $delimiter)
            {
                // Use the default delimiter
                $delimiter = self::$delimiter;
            }

            // Remove starting delimiters and spaces
            $path = ltrim($path, "{$delimiter} ");
            // Remove ending delimiters, spaces, and wildcards
            $path = rtrim($path, "{$delimiter} *");
            // Split the keys by delimiter
            $keys = explode($delimiter, $path);
        }

        do
        {
            $key = array_shift($keys);

            if (ctype_digit($key))
            {
                // Make the key an integer
                $key = (int) $key;
            }

            if (isset($array[$key]))
            {
                if ($keys)
                {
                    if (self::isArray($array[$key]))
                    {
                        // Dig down into the next part of the path
                        $array = $array[$key];
                    }
                    else
                    {
                        // Unable to dig deeper
                        break;
                    }
                }
                else
                {
                    // Found the path requested
                    return $array[$key];
                }
            }
            elseif ($key === '*')
            {
                // Handle wildcards

                $values = [];
                foreach ($array as $arr)
                {
                    if ($value = self::path($arr, implode('.', $keys)))
                    {
                        $values[] = $value;
                    }
                }

                if ($values)
                {
                    // Found the values requested
                    return $values;
                }
                else
                {
                    // Unable to dig deeper
                    break;
                }
            }
            else
            {
                // Unable to dig deeper
                break;
            }
        }
        while ($keys);

        // Unable to find the value requested
        return $default;
    }

    /**
     * Set a value on an array by path.
     *
     * @see self::path()
     *
     * @param array  $array     Array to update
     * @param string $path      Path
     * @param mixed  $value     Value to set
     * @param string $delimiter Path delimiter
     */
    public static function setPath(& $array, $path, $value, $delimiter = null)
    {
        if ( ! $delimiter)
        {
            // Use the default delimiter
            $delimiter = self::$delimiter;
        }

        // The path has already been separated into keys
        $keys = $path;
        if ( ! is_array($path))
        {
            // Split the keys by delimiter
            $keys = explode($delimiter, $path);
        }

        // Set current $array to inner-most array path
        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            if (ctype_digit($key))
            {
                // Make the key an integer
                $key = (int) $key;
            }

            if ( ! isset($array[$key]))
            {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        // Set key on inner-most array
        $array[array_shift($keys)] = $value;
    }

    /**
     * 指定步进和范围，生成数组
     *
     *     // 5, 10, 15, 20
     *     $values = self::range(5, 20);
     *
     * @param   integer $step 步进
     * @param   integer $max  最大数
     *
     * @return  array
     */
    public static function range($step = 10, $max = 100)
    {
        if ($step < 1)
        {
            return [];
        }

        $array = [];
        for ($i = $step; $i <= $max; $i += $step)
        {
            $array[$i] = $i;
        }

        return $array;
    }

    /**
     * 从指定数组中，读取指定键值
     *
     *     // 从$_POST中读取username
     *     $username = ArrayHelper::get($_POST, 'username');
     *     // 从$_GET中读取sorting
     *     $sorting = ArrayHelper::get($_GET, 'sorting');
     *
     * @param array|ArrayObject $target            Target to grab $key from
     * @param string            $key               Index into target to retrieve
     * @param mixed             $defaultValue      Value returned if $key is not in $target
     * @param bool              $emptyStringIsNull If true, and the result is an empty string (''), NULL is returned
     *
     * @return mixed
     */
    public static function get(array $target, $key, $defaultValue = null, $emptyStringIsNull = false)
    {
        $_result = is_array($target) ? (array_key_exists($key, $target) ? $target[$key] : $defaultValue) : $defaultValue;

        return $emptyStringIsNull && '' === $_result ? null : $_result;
    }

    /**
     * @param array|ArrayObject $target Target to check
     * @param string            $key    Key to check
     *
     * @return bool
     */
    public static function has(array $target, $key)
    {
        return is_array($target) && array_key_exists($key, $target);
    }

    /**
     * Ensures the argument passed in is actually an array with optional iteration callback
     *
     * @param array             $array
     * @param callable|\Closure $callback
     * @return array
     */
    public static function clean($array = null, $callback = null)
    {
        $_result = (empty($array) ? [] : (! is_array($array) ? [$array] : $array));

        if (null === $callback || ! is_callable($callback))
        {
            return $_result;
        }

        $_response = [];

        foreach ($_result as $_item)
        {
            $_response[] = call_user_func($callback, $_item);
        }

        return $_response;
    }

    /**
     * Retrieves multiple paths from an array. If the path does not exist in the
     * array, the default value will be added instead.
     *     // Get the values "username", "password" from $_POST
     *     $auth = ArrayHelper::extract($_POST, ['username', 'password']);
     *     // Get the value "level1.level2a" from $data
     *     $data = ['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']];
     *     ArrayHelper::extract($data, ['level1.level2a', 'password']);
     *
     * @param   array $array   array to extract paths from
     * @param   array $paths   list of path
     * @param   mixed $default default value
     *
     * @return  array
     */
    public static function extract($array, array $paths, $default = null)
    {
        $found = [];
        foreach ($paths as $path)
        {
            self::setPath($found, $path, self::path($array, $path, $default));
        }

        return $found;
    }

    /**
     * Retrieves multiple single-key values from a list of arrays.
     *
     *     // Get all of the "id" values from a result
     *     $ids = ArrayHelper::pluck($result, 'id');
     *
     * [!!] A list of arrays is an array that contains arrays, eg: [array $a, array $b, array $c, ...]
     *
     * @param   array  $array list of arrays to check
     * @param   string $key   key to pluck
     *
     * @return  array
     */
    public static function pluck($array, $key)
    {
        $values = [];

        foreach ($array as $row)
        {
            if (isset($row[$key]))
            {
                // Found a value in this row
                $values[] = $row[$key];
            }
        }

        return $values;
    }

    /**
     * Adds a value to the beginning of an associative array.
     *
     *     // Add an empty value to the start of a select list
     *     ArrayHelper::unshift($array, 'none', 'Select a value');
     *
     * @param   array  $array array to modify
     * @param   string $key   array key name
     * @param   mixed  $val   array value
     *
     * @return  array
     */
    public static function unshift(array & $array, $key, $val)
    {
        $array = array_reverse($array, true);
        $array[$key] = $val;
        $array = array_reverse($array, true);

        return $array;
    }

    /**
     * [array_map](http://php.net/array_map) 的强化版本
     *
     *     // Apply "strip_tags" to every element in the array
     *     $array = ArrayHelper::map('strip_tags', $array);
     *
     *     // Apply $this->filter to every element in the array
     *     $array = ArrayHelper::map([[$this,'filter']], $array);
     *
     *     // Apply strip_tags and $this->filter to every element
     *     $array = ArrayHelper::map(['strip_tags', [$this,'filter']), $array];
     *
     * [!!] Because you can pass an array of callbacks, if you wish to use an array-form callback
     * you must nest it in an additional array as above. Calling self::map([$this,'filter'], $array)
     * will cause an error.
     * [!!] Unlike `array_map`, this method requires a callback and will only map
     * a single array.
     *
     * @param   mixed $callbacks array of callbacks to apply to every element in the array
     * @param   array $array     array to map
     * @param   array $keys      array of keys to apply to
     *
     * @return  array
     */
    public static function map($callbacks, $array, $keys = null)
    {
        foreach ($array as $key => $val)
        {
            if (is_array($val))
            {
                $array[$key] = self::map($callbacks, $array[$key]);
            }
            elseif ( ! is_array($keys) || in_array($key, $keys))
            {
                if (is_array($callbacks))
                {
                    foreach ($callbacks as $cb)
                    {
                        $array[$key] = call_user_func($cb, $array[$key]);
                    }
                }
                else
                {
                    $array[$key] = call_user_func($callbacks, $array[$key]);
                }
            }
        }

        return $array;
    }

    /**
     * array_merge的强化版本。注意这个方法跟 [array_merge_recursive](http://php.net/array_merge_recursive) 是由区别的。
     * 具体区别看例子：
     *
     *     $john = ['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']];
     *     $mary = ['name' => 'mary', 'children' => ['jane']];
     *
     *     // John and Mary are married, merge them together
     *     $john = self::merge($john, $mary);
     *
     *     // The output of $john will now be:
     *     ['name' => 'mary', 'children' => ['fred', 'paul', 'sally', 'jane']]
     *
     * @param   array $array1     initial array
     * @param   array $array2,... array to merge
     *
     * @return  array
     */
    public static function merge($array1, $array2)
    {
        if (self::isAssoc($array2))
        {
            foreach ($array2 as $key => $value)
            {
                if (is_array($value)
                    && isset($array1[$key])
                    && is_array($array1[$key])
                )
                {
                    $array1[$key] = self::merge($array1[$key], $value);
                }
                else
                {
                    $array1[$key] = $value;
                }
            }
        }
        else
        {
            foreach ($array2 as $value)
            {
                if ( ! in_array($value, $array1, true))
                {
                    $array1[] = $value;
                }
            }
        }

        if (func_num_args() > 2)
        {
            foreach (array_slice(func_get_args(), 2) as $array2)
            {
                if (self::isAssoc($array2))
                {
                    foreach ($array2 as $key => $value)
                    {
                        if (is_array($value)
                            && isset($array1[$key])
                            && is_array($array1[$key])
                        )
                        {
                            $array1[$key] = self::merge($array1[$key], $value);
                        }
                        else
                        {
                            $array1[$key] = $value;
                        }
                    }
                }
                else
                {
                    foreach ($array2 as $value)
                    {
                        if ( ! in_array($value, $array1, true))
                        {
                            $array1[] = $value;
                        }
                    }
                }
            }
        }

        return $array1;
    }

    /**
     * Overwrites an array with values from input arrays.
     * Keys that do not exist in the first array will not be added!
     *     $a1 = ['name' => 'john', 'mood' => 'happy', 'food' => 'bacon'];
     *     $a2 = ['name' => 'jack', 'food' => 'tacos', 'drink' => 'beer'];
     *     // Overwrite the values of $a1 with $a2
     *     $array = ArrayHelper::overwrite($a1, $a2);
     *     // The output of $array will now be:
     *     ['name' => 'jack', 'mood' => 'happy', 'food' => 'tacos']
     *
     * @param   array $array1 master array
     * @param   array $array2 input arrays that will overwrite existing values
     *
     * @return  array
     */
    public static function overwrite($array1, $array2)
    {
        foreach (array_intersect_key($array2, $array1) as $key => $value)
        {
            $array1[$key] = $value;
        }

        if (func_num_args() > 2)
        {
            foreach (array_slice(func_get_args(), 2) as $array2)
            {
                foreach (array_intersect_key($array2, $array1) as $key => $value)
                {
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }

    /**
     * Creates a callable function and parameter list from a string representation.
     * Note that this function does not validate the callback string.
     *     // Get the callback function and parameters
     *     list($func, $params) = self::callback('Foo::bar(apple,orange)');
     *     // Get the result of the callback
     *     $result = call_user_func_array($func, $params);
     *
     * @param   string $str callback string
     *
     * @return  array   function, params
     */
    public static function callback($str)
    {
        // Overloaded as parts are found
        $params = null;

        // command[param,param]
        if (preg_match('/^([^\(]*+)\((.*)\)$/', $str, $match))
        {
            // command
            $command = $match[1];

            if ($match[2] !== '')
            {
                // param,param
                $params = preg_split('/(?<!\\\\),/', $match[2]);
                $params = str_replace('\,', ',', $params);
            }
        }
        else
        {
            // command
            $command = $str;
        }

        if (false !== strpos($command, '::'))
        {
            // Create a static method callable command
            $command = explode('::', $command, 2);
        }

        return [
            $command,
            $params
        ];
    }

    /**
     * Convert a multi-dimensional array into a single-dimensional array.
     *     $array = ['set' => ['one' => 'something'], 'two' => 'other'];
     *     // Flatten the array
     *     $array = ArrayHelper::flatten($array);
     *     // The array will now be
     *     [('one' => 'something', 'two' => 'other'];
     * [!!] The keys of array values will be discarded.
     *
     * @param   array $array array to flatten
     *
     * @return  array
     */
    public static function flatten($array)
    {
        $isAssoc = self::isAssoc($array);

        $flat = [];
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $flat = array_merge($flat, self::flatten($value));
            }
            else
            {
                if ($isAssoc)
                {
                    $flat[$key] = $value;
                }
                else
                {
                    $flat[] = $value;
                }
            }
        }

        return $flat;
    }

    /**
     * 将关联数组的属性-值copy到对象上去
     *     ArrayHelper::mix($object, ['foo' => 'bar'], true);
     *
     * @copyright   https://github.com/akira-cn/JKit
     *
     * @param       mixed   $obj      被copy到的对象
     * @param       array   $hash     关联数组
     * @param       boolean $override 是否覆盖对象上已有属性
     *
     * @return      mixed    被copy到的对象
     */
    public static function mix($obj, $hash, $override = false)
    {
        foreach ($hash as $key => $value)
        {
            if ($override || ! isset($obj->{$key}))
            {
                $obj->{$key} = $value;
            }
        }

        return $obj;
    }


    /**
     * Unset all keys that have empty string values
     *
     * @static
     *
     * @param array $input
     *
     * @return array
     */
    public static function removeEmpty(array $input)
    {
        if ( ! is_array($input) || empty($input))
        {
            return [];
        }
        foreach ($input as $key => $value)
        {
            if (is_string($value) && $value == '')
            {
                unset($input[$key]);
            }
        }

        return $input;
    }

    /**
     * Removes items with null value from an array.
     *
     * @param array $array
     */
    public static function removeNull(array & $array)
    {
        foreach ($array as $key => $value)
        {
            if (null === $value)
            {
                unset($array[$key]);
            }
        }
    }

    /**
     * Convert an iterator to an array.
     * Converts an iterator to an array. The $recursive flag, on by default,
     * hints whether or not you want to do so recursively.
     *
     * @param  array|Traversable $iterator  The array or Traversable object to convert
     * @param  bool              $recursive Recursively check all nested structures
     *
     * @throws InvalidArgumentException if $iterator is not an array or a Traversable object
     * @return array
     */
    public static function iteratorToArray($iterator, $recursive = true)
    {
        if ( ! is_array($iterator) && ! $iterator instanceof Traversable)
        {
            throw new InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        if ( ! $recursive)
        {
            if (is_array($iterator))
            {
                return $iterator;
            }

            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray'))
        {
            return $iterator->toArray();
        }

        $array = [];
        foreach ($iterator as $key => $value)
        {
            if (is_scalar($value))
            {
                $array[$key] = $value;
                continue;
            }

            if ($value instanceof Traversable)
            {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            if (is_array($value))
            {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }
}

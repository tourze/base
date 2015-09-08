<?php

namespace tourze\Base\Helper;

use Traversable;

/**
 * 数组助手类
 *
 * @package tourze\Base\Helper
 */
class Arr extends HelperBase implements HelperInterface
{

    /**
     * @var string `path()`使用的分隔符
     */
    public static $delimiter = '.';

    /**
     * 指定数组是否为一个关联数组
     *
     *     // true
     *     Arr::isAssoc(['username' => 'john.doe']);
     *
     *     // false
     *     Arr::isAssoc(['foo', 'bar']);
     *
     * @param  mixed $array 要检查的数组
     * @return bool
     */
    public static function isAssoc($array)
    {
        // 如果不是个规则数组，那就直接返回false
        if ( ! is_array($array))
        {
            return false;
        }

        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * 检查参数是否为一个数组或数组对象
     *
     *     // true
     *     Arr::isArray([]);
     *     Arr::isArray(new ArrayObject);
     *
     *     // false
     *     Arr::isArray(false);
     *     Arr::isArray('not an array!');
     *     Arr::isArray(Db::instance());
     *
     * @param  mixed $value 要检查的值
     * @return bool
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
     * 使用路径格式来读取一个数组值
     *
     *     // $array['foo']['bar']
     *     $value = Arr::path($array, 'foo.bar');
     *
     * 这里也可以使用通配符“*”
     *
     *     // 返回所有三级的color
     *     $colors = Arr::path($array, 'theme.*.color');
     *     // 跟上面一样效果
     *     $colors = Arr::path($array, ['theme', '*', 'color']);
     *
     * @param  array  $array     要搜索的数组
     * @param  mixed  $path      path值，或者数组
     * @param  mixed  $default   默认返回值
     * @param  string $delimiter 自定义分隔符
     * @return mixed
     */
    public static function path($array, $path, $default = null, $delimiter = null)
    {
        if ( ! self::isArray($array))
        {
            return $default;
        }

        if (is_array($path))
        {
            $keys = $path;
        }
        else
        {
            // 如果直接就存在这样的path名，那么直接返回
            if (array_key_exists($path, $array))
            {
                return $array[$path];
            }

            // 使用默认分隔符
            if (null === $delimiter)
            {
                $delimiter = self::$delimiter;
            }

            // 移除左右的空格和分隔符，并分割成数组
            $path = ltrim($path, "{$delimiter} ");
            $path = rtrim($path, "{$delimiter} *");
            $keys = explode($delimiter, $path);
        }

        do
        {
            $key = array_shift($keys);

            if (ctype_digit($key))
            {
                // 如果为数字格式的话，那就强制转换为整形
                $key = (int) $key;
            }

            if (isset($array[$key]))
            {
                if ($keys)
                {
                    if (self::isArray($array[$key]))
                    {
                        // 循环读取下一节
                        $array = $array[$key];
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    // 找到结果啦
                    return $array[$key];
                }
            }
            // 通配符处理
            elseif ($key === '*')
            {
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
                    return $values;
                }
                else
                {
                    break;
                }
            }
            else
            {
                break;
            }
        }
        while ($keys);

        // 查找不到，返回默认值
        return $default;
    }

    /**
     * 根据路径来设置值
     *
     * @param array  $array     要更新的数组
     * @param string $path      路径名
     * @param mixed  $value     要更新的值
     * @param string $delimiter 自定义分隔符
     */
    public static function setPath(& $array, $path, $value, $delimiter = null)
    {
        // 使用默认分隔符
        if ( ! $delimiter)
        {
            $delimiter = self::$delimiter;
        }

        // 处理path节
        $keys = $path;
        if ( ! is_array($path))
        {
            $keys = explode($delimiter, $path);
        }

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            if (ctype_digit($key))
            {
                $key = (int) $key;
            }

            if ( ! isset($array[$key]))
            {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    /**
     * 指定步进和范围，生成数组
     *
     *     // 5, 10, 15, 20
     *     $values = Arr::range(5, 20);
     *
     * @param  int $step 步进
     * @param  int $max  最大数
     * @return array
     */
    public static function range($step = 10, $max = 100)
    {
        $step = intval($step);
        $max = intval($max);

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
     *     $username = Arr::get($_POST, 'username');
     *
     *     // 从$_GET中读取sorting
     *     $sorting = Arr::get($_GET, 'sorting');
     *
     * @param array  $array        要读取的数组
     * @param string $key          索引值
     * @param mixed  $defaultValue 默认返回值
     * @param bool   $emptyNull    如果设置为true，而且结果是空字符串，此时返回null
     * @return mixed
     */
    public static function get(array $array, $key, $defaultValue = null, $emptyNull = false)
    {
        $result = is_array($array)
            ? (array_key_exists($key, $array) ? $array[$key] : $defaultValue)
            : $defaultValue;

        return $emptyNull && '' === $result ? null : $result;
    }

    /**
     * 查看目标是否有指定的key
     *
     * @param mixed  $array 要检查的数组
     * @param string $key   要检查的值
     * @return bool
     */
    public static function has($array, $key)
    {
        return is_array($array) && array_key_exists($key, $array);
    }

    /**
     * 读取数组中指定的若干个key，可以传path格式
     *
     *     // 从$_POST中读取"username"和"password"
     *     $auth = Arr::extract($_POST, ['username', 'password']);
     *
     *     // 读取"level1.level2a"
     *     $data = ['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']];
     *     Arr::extract($data, ['level1.level2a', 'password']);
     *
     * [!!] 注意，这个方法跟[Arr::get]是有区别的。[Arr::get]不支持path方式读取。
     *
     * @param  array $array   要读取的数组
     * @param  array $paths   键值或path列表，如果这个参数是个关联数组，那么数组中的值会作为该key的默认值
     * @param  mixed $default 默认值
     * @return array
     */
    public static function extract($array, $paths, $default = null)
    {
        $found = [];

        if ( ! is_array($paths))
        {
            $paths = [$paths];
        }

        // 如果paths不是个关联数组，那么就生成个值做为key，默认值作为值的新数组
        if ( ! self::isAssoc($paths))
        {
            $paths = array_values($paths);
            $newPaths = [];
            foreach ($paths as $path)
            {
                $newPaths[$path] = $default;
            }
            $paths = $newPaths;
        }

        foreach ($paths as $path => $pathDefault)
        {
            self::setPath($found, $path, self::path($array, $path, $pathDefault));
        }

        return $found;
    }

    /**
     * 在数组开头增加一个元素
     *
     *     Arr::unshift($array, 'none', 'Select a value');
     *
     * @param  array  $array 要更改的数组
     * @param  string $key   键名
     * @param  mixed  $val   键值
     * @return array
     */
    public static function unshift(array & $array, $key, $val)
    {
        $array = array_reverse($array, true);
        $array[$key] = $val;
        $array = array_reverse($array, true);

        return $array;
    }

    /**
     * 跟 [array_map](http://php.net/array_map) 类似的函数，差异在于这个函数只能处理单个数组
     *
     *     // 数组中的每个元素都执行一次strip_tags
     *     $array = Arr::map($array, 'strip_tags');
     *
     *     // 数组中的每个元素都$this->filter过滤一次
     *     $array = Arr::map($array, [[$this,'filter']]);
     *
     *     // 数组中的每个元素都执行strip_tags和$this->filter
     *     $array = Arr::map($array, ['strip_tags', [$this,'filter'])];
     *
     * @param  array    $array     要映射的数组
     * @param  callable $callbacks 包含了callback的数组，或者一个函数
     * @param  array    $keys      只过滤这部分key
     * @return array
     */
    public static function map($array, $callbacks, $keys = null)
    {
        foreach ($array as $key => $val)
        {
            if (is_array($val))
            {
                $array[$key] = self::map($array[$key], $callbacks);
            }
            elseif ( ! is_array($keys) || in_array($key, $keys))
            {
                if (is_array($callbacks))
                {
                    // 如果callbacks变量的第一个参数是object，那么久按照另外的逻辑处理
                    if (is_object(Arr::get($callbacks, 0)))
                    {
                        $callbacks = [
                            [Arr::get($callbacks, 0), Arr::get($callbacks, 1)]
                        ];
                        // 后面的参数暂时忽略了
                    }
                    // 继续正常逻辑走
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
     * array_merge的强化版本。注意这个方法跟 [array_merge_recursive](http://php.net/array_merge_recursive) 是有区别的。
     * 如果要合并的项是数组，那么合并数组，否则使用后者的值来代替前者。
     *
     *     $john = ['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']];
     *     $mary = ['name' => 'mary', 'children' => ['jane']];
     *
     *     $john = self::merge($john, $mary);
     *
     *     // 输出
     *     ['name' => 'mary', 'children' => ['fred', 'paul', 'sally', 'jane']]
     *
     * @param  array $array1     初始数组
     * @param  array $array2,... 要合并的数组
     * @return array
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
     * 转换一个多维数组为一维数组
     *
     *     $array = ['set' => ['one' => 'something'], 'two' => 'other'];
     *
     *     $array = Arr::flatten($array);
     *
     *     // 输出
     *     ['one' => 'something', 'two' => 'other'];
     *
     * [!!] 键名会被忽略
     *
     * @param  array $array 要处理的数组
     * @return array
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
}

<?php

namespace tourze\Base\Security;

use ArrayAccess;
use ReflectionFunction;
use ReflectionMethod;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Object;
use tourze\Base\Message;

/**
 * 数组校验类
 *
 * @property string errorFileName
 * @package    Base
 * @category   Security
 * @author     YwiSax
 */
class Validation extends Object implements ArrayAccess
{

    /**
     * @var string 助手类名称
     */
    public static $validHelperClass = 'tourze\Base\Security\Valid';

    /**
     * 创建一个新的校验实例
     *
     * @param  array $array 要检测的数组内容
     * @return static
     */
    public static function factory(array $array)
    {
        return new self([
            '_data' => $array
        ]);
    }

    /**
     * @var array 要绑定的值
     */
    protected $_bound = [];

    /**
     * @var array 处理规则
     */
    protected $_rules = [];

    /**
     * @var array 字段描述
     */
    protected $_labels = [];

    /**
     * @var string 错误文本对应的文件名
     */
    protected $_errorFileName = null;

    /**
     * @var array 内容为空时的规则
     */
    protected $_emptyRules = [
        'notEmpty',
        'matches'
    ];

    /**
     * @var array 错误列表，field => rule
     */
    protected $_errors = [];

    /**
     * @var array 要检查的数据列表
     */
    protected $_data = [];

    /**
     * validation对象是只读的，不能直接访问内部数据
     *
     * @throws BaseException
     * @param  string $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new BaseException('Validation objects are read-only.');
    }

    /**
     * 检测指定键是否存在
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * 不能直接unset
     *
     * @throws BaseException
     * @param  string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new BaseException('Validation objects are read-only.');
    }

    /**
     * 从数组数据中读取指定值
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    /**
     * 复制当前规则给新的数组
     *
     *     $copy = $array->copy($newData);
     *
     * @param  array $array 新的数组
     * @return static
     */
    public function copy(array $array)
    {
        $copy = clone $this;
        $copy->setData($array);

        return $copy;
    }

    /**
     * 为指定字段设置标签值
     *
     * @param  string $field 字段名
     * @param  string $label 标签
     * @return $this
     */
    public function label($field, $label)
    {
        $this->_labels[$field] = $label;

        return $this;
    }

    /**
     * 设置多个字段的标签值
     *
     * @param  array $labels
     * @return $this
     */
    public function labels(array $labels)
    {
        foreach ($labels as $field => $label)
        {
            $this->label($field, $label);
        }

        return $this;
    }

    /**
     * 为一个字段覆盖或者添加新的规则，每条规则都只执行一次；你可以在规则中使用下面别名：
     *
     * - :validation - 当前校验对象
     * - :field - 字段名
     * - :value - 当前值
     *
     *     // username不能为空，并且最小长度为4
     *     $validation->rule('username', 'not_empty')
     *                ->rule('username', 'minLength', [':value', 4]);
     *
     *     // password必须跟password_repeat字段内容一致
     *     $validation->rule('password', 'matches', [':validation', 'password', 'password_repeat']);
     *
     *     // 也可以使用匿名函数
     *     $validation->rule('index',
     *         function(Validation $array, $field, $value)
     *         {
     *             if ($value > 6 && $value < 10)
     *             {
     *                 $array->error($field, 'custom');
     *             }
     *         }
     *         , [':validation', ':field', ':value']
     *     );
     *
     * [!!] 如果使用匿名函数的话，必须手动添加错误信息，这时候校验类不会自动判断并加载message的了
     *
     * @param  string   $field  字段名
     * @param  callback $rule   PHP回调函数，或者一个匿名函数
     * @param  array    $params 参数列表
     * @return $this
     */
    public function rule($field, $rule, array $params = null)
    {
        if (null === $params)
        {
            // 默认只有[':value']
            $params = [':value'];
        }

        if (true !== $field && ! isset($this->_labels[$field]))
        {
            $this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
        }

        // 保存规则
        $this->_rules[$field][] = [
            $rule,
            $params
        ];

        return $this;
    }

    /**
     * 一次性添加多条规则
     *
     * @param  string $field 字段名
     * @param  array  $rules 规则
     * @return $this
     */
    public function rules($field, array $rules)
    {
        foreach ($rules as $rule)
        {
            $this->rule($field, $rule[0], Arr::get($rule, 1));
        }

        return $this;
    }

    /**
     * 绑定一个参数
     *
     *     $validation->bind(':model', $model)->rule('status', 'valid_status', [':model']);
     *
     * @param  string $key   变量名或包含了变量名的数组
     * @param  mixed  $value 值
     * @return $this
     */
    public function bind($key, $value = null)
    {
        if (is_array($key))
        {
            foreach ($key as $name => $value)
            {
                $this->_bound[$name] = $value;
            }
        }
        else
        {
            $this->_bound[$key] = $value;
        }

        return $this;
    }

    /**
     * 执行检测
     *
     *     if ($validation->check())
     *     {
     *          // 校验通过
     *     }
     *
     * @return bool
     */
    public function check()
    {
        // 清空数据和错误
        $data = $this->_errors = [];

        // 保存原来的数据
        $original = $this->_data;

        // 读取字段信息
        $fields = Arr::merge(array_keys($original), array_keys($this->_labels));

        // 现在的规则
        $rules = $this->_rules;

        // 逐个字段格式化下规则
        foreach ($fields as $field)
        {
            $data[$field] = Arr::get($this->_data, $field);

            // 如果key为true，那么规则就是所有字段都生效
            if (isset($rules[true]))
            {
                if ( ! isset($rules[$field]))
                {
                    $rules[$field] = [];
                }
                $rules[$field] = array_merge($rules[$field], $rules[true]);
            }
        }

        // 保存格式化后的规则
        $this->_data = $data;

        // 清空全局规则
        unset($rules[true]);

        // 绑定一些必要的变量
        $this->bind(':validation', $this);
        $this->bind(':data', $this->_data);

        foreach ($rules as $field => $set)
        {
            $value = Arr::get($this->_data, $field);

            // 绑定当前处理的字段和值
            $this->bind([
                ':field' => $field,
                ':value' => $value,
            ]);

            foreach ($set as $array)
            {
                // 格式：[$rule, $params]
                $rule = array_shift($array);
                $params = array_shift($array);

                foreach ($params as $key => $param)
                {
                    // 替换绑定的变量
                    if (is_string($param) && array_key_exists($param, $this->_bound))
                    {
                        $params[$key] = $this->_bound[$param];
                    }
                }

                // 默认错误名跟规则名一致，因为一般规则都是字符串表示的
                $errorName = $rule;

                // 规则是数组，说明传入的是一个callback
                if (is_array($rule))
                {
                    // 允许rule('field', [':model', 'some_rule']);
                    if (is_string($rule[0]) && array_key_exists($rule[0], $this->_bound))
                    {
                        $rule[0] = $this->_bound[$rule[0]];
                    }

                    // 把索引为1的值作为错误标记
                    $errorName = $rule[1];
                    $passed = call_user_func_array($rule, $params);
                }
                elseif ( ! is_string($rule))
                {
                    // 匿名函数不做错误处理，留待内容做处理
                    $errorName = false;
                    $passed = call_user_func_array($rule, $params);
                }
                elseif (method_exists(self::$validHelperClass, $rule))
                {
                    // 调用校验助手类来完成操作
                    $method = new ReflectionMethod(self::$validHelperClass, $rule);
                    $passed = $method->invokeArgs(null, $params);
                }
                elseif (false === strpos($rule, '::'))
                {
                    // 不符合静态方法调用的规则，那么直接按照函数调用
                    $function = new ReflectionFunction($rule);
                    $passed = $function->invokeArgs($params);
                }
                else
                {
                    // 当做一次静态函数调用
                    $temp = explode('::', $rule, 2);
                    $class = array_shift($temp);
                    $method = array_shift($temp);
                    $method = new ReflectionMethod($class, $method);
                    $passed = $method->invokeArgs(null, $params);
                }

                if ( ! in_array($rule, $this->_emptyRules) && ! Valid::notEmpty($value))
                {
                    continue;
                }

                if (false === $passed && false !== $errorName)
                {
                    $this->error($field, $errorName, $params);
                    break;
                }
                elseif (isset($this->_errors[$field]))
                {
                    // 匿名函数自己处理错误信息，这里就不处理了
                    break;
                }
            }
        }

        // 还原原来的值
        $this->_data = $original;

        return empty($this->_errors);
    }

    /**
     * 添加指定字段的错误
     *
     * @param  string $field 字段名
     * @param  string $error 错误信息
     * @param  array  $params
     * @return $this
     */
    public function error($field, $error, array $params = null)
    {
        $this->_errors[$field] = [
            $error,
            $params
        ];

        return $this;
    }

    /**
     * 返回处理后的错误信息
     *
     *     // 从message/forms/authorize.php读取错误信息
     *     $errors = $validation->errors('forms/login');
     *
     * @param  string $file      要读取的消息文本
     * @param  mixed  $translate 是否翻译
     * @return array
     */
    public function errors($file = null, $translate = true)
    {
        if (null === $file)
        {
            if ( ! $this->getErrorFileName())
            {
                return $this->_errors;
            }
            $file = $this->getErrorFileName();
        }

        $messages = [];

        foreach ($this->_errors as $field => $set)
        {
            $error = array_shift($set);
            $params = array_shift($set);

            $label = $this->_labels[$field];

            if ($translate)
            {
                $label = is_string($translate)
                    ? __($label, null, $translate)
                    : __($label);
            }

            $values = [
                ':field' => $label,
                ':value' => Arr::get((array) $this, $field),
            ];

            if (is_array($values[':value']))
            {
                $values[':value'] = implode(', ', Arr::flatten($values[':value']));
            }

            if ($params)
            {
                foreach ($params as $key => $value)
                {
                    if (is_array($value))
                    {
                        $value = implode(', ', Arr::flatten($value));
                    }
                    elseif (is_object($value))
                    {
                        // 消息文件中不能使用对象
                        continue;
                    }

                    if (isset($this->_labels[$value]))
                    {
                        // 使用标签值作为值
                        $value = $this->_labels[$value];
                        if ($translate)
                        {
                            $value = is_string($translate)
                                ? __($value, null, $translate)
                                : __($value);
                        }
                    }

                    // 绑定参数，从1开始
                    $values[':param' . ($key + 1)] = $value;
                }
            }

            // 直接读消息文本
            $message = Message::load($file, "{$field}.{$error}");

            // 尝试读取字段的默认说明
            if ( ! $message)
            {
                $message = Message::load($file, "{$field}.default");
            }

            // 尝试读取这个错误的通用提示
            if ( ! $message)
            {
                $message = Message::load($file, $error);
            }

            // 从默认的校验错误列表中读取信息
            if ( ! $message)
            {
                $message = Message::load('validation', $error);
            }

            // 最后都还是不行，那就直接返回
            if ( ! $message)
            {
                $message = "{$file}.{$field}.{$error}";
            }

            if ($translate)
            {
                $message = is_string($translate)
                    ? __($message, $values, $translate)
                    : __($message, $values);
            }
            else
            {
                $message = strtr($message, $values);
            }

            $messages[$field] = $message;
        }

        return $messages;
    }

    /**
     * @return string
     */
    public function getErrorFileName()
    {
        return $this->_errorFileName;
    }

    /**
     * @param string $errorFileName
     */
    public function setErrorFileName($errorFileName)
    {
        $this->_errorFileName = $errorFileName;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

}

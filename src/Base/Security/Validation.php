<?php

namespace tourze\Base\Security;

use ArrayAccess;
use ReflectionFunction;
use ReflectionMethod;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Object;
use tourze\Base\Base;

/**
 * Array and variable validation.
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
     * Creates a new Validation instance.
     *
     * @param   array $array array to use for validation
     *
     * @return  Validation
     */
    public static function factory(array $array)
    {
        return new Validation($array);
    }

    /**
     * @var array  要绑定的值
     */
    protected $_bound = [];

    /**
     * @var array  处理规则
     */
    protected $_rules = [];

    // Field labels
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
     * @var array  错误列表，field => rule
     */
    protected $_errors = [];

    /**
     * @var array  要检查的数据列表
     */
    protected $_data = [];

    /**
     * Sets the unique "any field" key and creates an ArrayObject from the
     * passed array.
     *
     * @param   array $array array to validate
     */
    public function __construct(array $array)
    {
        $this->_data = $array;
    }

    /**
     * Throws an exception because Validation is read-only.
     * Implements ArrayAccess method.
     *
     * @throws  BaseException
     *
     * @param   string $offset key to set
     * @param   mixed  $value  value to set
     *
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        throw new BaseException('Validation objects are read-only.');
    }

    /**
     * Checks if key is set in array data.
     * Implements ArrayAccess method.
     *
     * @param   string $offset key to check
     *
     * @return  bool    whether the key is set
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * Throws an exception because Validation is read-only.
     * Implements ArrayAccess method.
     *
     * @throws  BaseException
     *
     * @param   string $offset key to unset
     *
     * @return  void
     */
    public function offsetUnset($offset)
    {
        throw new BaseException('Validation objects are read-only.');
    }

    /**
     * Gets a value from the array data.
     * Implements ArrayAccess method.
     *
     * @param   string $offset key to return
     *
     * @return  mixed   value from array
     */
    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    /**
     * Copies the current rules to a new array.
     *     $copy = $array->copy($new_data);
     *
     * @param   array $array new data set
     *
     * @return  Validation
     */
    public function copy(array $array)
    {
        // Create a copy of the current validation set
        $copy = clone $this;
        // Replace the data set
        $copy->_data = $array;

        return $copy;
    }

    /**
     * Returns the array of data to be validated.
     *
     * @return  array
     */
    public function data()
    {
        return $this->_data;
    }

    /**
     * Sets or overwrites the label name for a field.
     *
     * @param   string $field field name
     * @param   string $label label
     *
     * @return  $this
     */
    public function label($field, $label)
    {
        // Set the label for this field
        $this->_labels[$field] = $label;

        return $this;
    }

    /**
     * Sets labels using an array.
     *
     * @param   array $labels list of field => label names
     *
     * @return  $this
     */
    public function labels(array $labels)
    {
        $this->_labels = $labels + $this->_labels;

        return $this;
    }

    /**
     * Overwrites or appends rules to a field. Each rule will be executed once.
     * All rules must be string names of functions method names. Parameters must
     * match the parameters of the callback function exactly
     * Aliases you can use in callback parameters:
     * - :validation - the validation object
     * - :field - the field name
     * - :value - the value of the field
     *     // The "username" must not be empty and have a minimum length of 4
     *     $validation->rule('username', 'not_empty')
     *                ->rule('username', 'minLength', [':value', 4]);
     *     // The "password" field must match the "password_repeat" field
     *     $validation->rule('password', 'matches', [':validation', 'password', 'password_repeat']);
     *     // Using closure (anonymous function)
     *     $validation->rule('index',
     *         function(Validation $array, $field, $value)
     *         {
     *             if ($value > 6 AND $value < 10)
     *             {
     *                 $array->error($field, 'custom');
     *             }
     *         }
     *         , [':validation', ':field', ':value']
     *     );
     * [!!] Errors must be added manually when using closures!
     *
     * @param   string   $field  field name
     * @param   callback $rule   valid PHP callback or closure
     * @param   array    $params extra parameters for the rule
     *
     * @return  $this
     */
    public function rule($field, $rule, array $params = null)
    {
        if (null === $params)
        {
            // Default to [':value']
            $params = [':value'];
        }

        if (true !== $field && ! isset($this->_labels[$field]))
        {
            // Set the field label to the field name
            $this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
        }

        // Store the rule and params for this rule
        $this->_rules[$field][] = [
            $rule,
            $params
        ];

        return $this;
    }

    /**
     * Add rules using an array.
     *
     * @param   string $field field name
     * @param   array  $rules list of callbacks
     *
     * @return  $this
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
     * Bind a value to a parameter definition.
     *     // This allows you to use :model in the parameter definition of rules
     *     $validation->bind(':model', $model)
     *         ->rule('status', 'valid_status', [':model']);
     *
     * @param   string $key   variable name or an array of variables
     * @param   mixed  $value value
     *
     * @return  $this
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
     * Executes all validation rules. This should
     * typically be called within an if/else block.
     *     if ($validation->check())
     *     {
     *          // The data is valid, do something here
     *     }
     *
     * @return  boolean
     */
    public function check()
    {
        // New data set
        $data = $this->_errors = [];

        // 保存原来的数据
        $original = $this->_data;
        // Get a list of the expected fields
        $expected = Arr::merge(array_keys($original), array_keys($this->_labels));

        // Import the rules locally
        $rules = $this->_rules;

        foreach ($expected as $field)
        {
            // Use the submitted value or null if no data exists
            $data[$field] = Arr::get($this->_data, $field);

            if (isset($rules[true]))
            {
                if ( ! isset($rules[$field]))
                {
                    // Initialize the rules for this field
                    $rules[$field] = [];
                }

                // Append the rules
                $rules[$field] = array_merge($rules[$field], $rules[true]);
            }
        }

        // Overload the current array with the new one
        $this->_data = $data;

        // Remove the rules that apply to every field
        unset($rules[true]);

        // Bind the validation object to :validation
        $this->bind(':validation', $this);
        // Bind the data to :data
        $this->bind(':data', $this->_data);

        foreach ($rules as $field => $set)
        {
            $value = Arr::get($this->_data, $field);

            // Bind the field name and value to :field and :value respectively
            $this->bind([
                ':field' => $field,
                ':value' => $value,
            ]);

            foreach ($set as $array)
            {
                // Rules are defined as [$rule, $params]
                list($rule, $params) = $array;

                foreach ($params as $key => $param)
                {
                    if (is_string($param) && array_key_exists($param, $this->_bound))
                    {
                        // Replace with bound value
                        $params[$key] = $this->_bound[$param];
                    }
                }

                // Default the error name to be the rule (except array and lambda rules)
                $errorName = $rule;

                if (is_array($rule))
                {
                    // Allows rule('field', [':model', 'some_rule']);
                    if (is_string($rule[0]) && array_key_exists($rule[0], $this->_bound))
                    {
                        // Replace with bound value
                        $rule[0] = $this->_bound[$rule[0]];
                    }

                    // This is an array callback, the method name is the error name
                    $errorName = $rule[1];
                    $passed = call_user_func_array($rule, $params);
                }
                elseif ( ! is_string($rule))
                {
                    // This is a lambda function, there is no error name (errors must be added manually)
                    $errorName = false;
                    $passed = call_user_func_array($rule, $params);
                }
                elseif (method_exists(self::$validHelperClass, $rule))
                {
                    // Use a method in this object
                    $method = new ReflectionMethod(self::$validHelperClass, $rule);

                    // Call static::$rule($this[$field], $param, ...) with Reflection
                    $passed = $method->invokeArgs(null, $params);
                }
                elseif (false === strpos($rule, '::'))
                {
                    // Use a function call
                    $function = new ReflectionFunction($rule);
                    // Call $function($this[$field], $param, ...) with Reflection
                    $passed = $function->invokeArgs($params);
                }
                else
                {
                    // Split the class and method of the rule
                    list($class, $method) = explode('::', $rule, 2);

                    // Use a static method call
                    $method = new ReflectionMethod($class, $method);

                    // Call $Class::$method($this[$field], $param, ...) with Reflection
                    $passed = $method->invokeArgs(null, $params);
                }

                // Ignore return values from rules when the field is empty
                if ( ! in_array($rule, $this->_emptyRules) && ! Valid::notEmpty($value))
                {
                    continue;
                }

                if (false === $passed && false !== $errorName)
                {
                    // Add the rule to the errors
                    $this->error($field, $errorName, $params);

                    // This field has an error, stop executing rules
                    break;
                }
                elseif (isset($this->_errors[$field]))
                {
                    // The callback added the error manually, stop checking rules
                    break;
                }
            }
        }

        // Restore the data to its original form
        $this->_data = $original;

        return empty($this->_errors);
    }

    /**
     * Add an error to a field.
     *
     * @param   string $field field name
     * @param   string $error error message
     * @param   array  $params
     *
     * @return  $this
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
     *     // Get errors from messages/forms/authorize.php
     *     $errors = $Validation->errors('forms/login');
     *
     * @uses    Base::message
     * @param   string $file      file to load error messages from
     * @param   mixed  $translate translate the message
     * @return  array
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
            list($error, $params) = $set;

            // Get the label for this field
            $label = $this->_labels[$field];

            if ($translate)
            {
                if (is_string($translate))
                {
                    // Translate the label using the specified language
                    $label = __($label, null, $translate);
                }
                else
                {
                    // Translate the label
                    $label = __($label);
                }
            }

            // Start the translation values list
            $values = [
                ':field' => $label,
                ':value' => Arr::get((array) $this, $field),
            ];

            if (is_array($values[':value']))
            {
                // All values must be strings
                $values[':value'] = implode(', ', Arr::flatten($values[':value']));
            }

            if ($params)
            {
                foreach ($params as $key => $value)
                {
                    if (is_array($value))
                    {
                        // All values must be strings
                        $value = implode(', ', Arr::flatten($value));
                    }
                    elseif (is_object($value))
                    {
                        // Objects cannot be used in message files
                        continue;
                    }

                    // Check if a label for this parameter exists
                    if (isset($this->_labels[$value]))
                    {
                        // Use the label as the value, eg: related field name for "matches"
                        $value = $this->_labels[$value];

                        if ($translate)
                        {
                            if (is_string($translate))
                            {
                                // Translate the value using the specified language
                                $value = __($value, null, $translate);
                            }
                            else
                            {
                                // Translate the value
                                $value = __($value);
                            }
                        }
                    }

                    // Add each parameter as a numbered value, starting from 1
                    $values[':param' . ($key + 1)] = $value;
                }
            }

            $message = Base::message($file, "{$field}.{$error}");
            if ($message && is_string($message))
            {
                // Found a message for this field and error
            }
            elseif ($message = Base::message($file, "{$field}.default") && is_string($message))
            {
                // Found a default message for this field
            }
            elseif ($message = Base::message($file, $error) && is_string($message))
            {
                // Found a default message for this error
            }
            elseif ($message = Base::message('validation', $error) && is_string($message))
            {
                // Found a default message for this error
            }
            else
            {
                // No message exists, display the path expected
                $message = "{$file}.{$field}.{$error}";
            }

            if ($translate)
            {
                if (is_string($translate))
                {
                    // Translate the message using specified language
                    $message = __($message, $values, $translate);
                }
                else
                {
                    // Translate the message using the default language
                    $message = __($message, $values);
                }
            }
            else
            {
                // Do not translate, just replace the values
                $message = strtr($message, $values);
            }

            // Set the message for this field
            $messages[$field] = $message;
        }

        return $messages;
    }

    /**
     * @return null
     */
    public function getErrorFileName()
    {
        return $this->_errorFileName;
    }

    /**
     * @param null $errorFileName
     */
    public function setErrorFileName($errorFileName)
    {
        $this->_errorFileName = $errorFileName;
    }

}

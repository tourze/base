<?php

namespace tourze\Base;

use Doctrine\Common\Inflector\Inflector;
use Exception;
use tourze\Base\Exception\BaseException;
use tourze\Base\Exception\InvalidCallException;
use tourze\Base\Exception\UnknownPropertyException;

/**
 * Yii2中抽取的Object类，进行了部分修改，使其更适应框架本身
 *
 * @package tourze\Base
 */
class Object
{

    /**
     * @var array 保存实例列表
     */
    protected static $_instances = [];

    /**
     * 为保证不重复，根据参数生成instance key
     *
     * @param mixed $args
     * @return string
     */
    protected static function instanceKey($args = null)
    {
        $class = self::className();

        if (is_array($args))
        {
            $instanceKey = md5(json_encode($args));
        }
        elseif ($args === null)
        {
            $instanceKey = 'default';
        }
        else
        {
            $instanceKey = (string) $args;
        }
        $instanceKey = $class . ':' . $instanceKey;

        return $instanceKey;
    }

    /**
     * 返回指定实例
     *
     * @static
     * @access public
     * @param  mixed $args 实例传参，直接传给构造方法
     * @return $this
     */
    public static function instance($args = null)
    {
        $class = self::className();
        $instanceKey = self::instanceKey($args);

        if ( ! isset(self::$_instances[$instanceKey]))
        {
            self::$_instances[$instanceKey] = is_array($args) ? new $class($args) : new $class;
        }
        return self::$_instances[$instanceKey];
    }

    /**
     * 返回当前类的类名，关键词：__延时加载__
     *
     * @static
     * @access public
     * @return string
     */
    final public static function className()
    {
        return get_called_class();
    }

    /**
     * 返回当前类的命名空间
     */
    final public static function namespaceName()
    {
        $class = self::className();
        $data = explode('\\', $class);

        if (count($data) == 1)
        {
            return '\\';
        }
        else
        {
            array_pop($data);
            return '\\' . implode('\\', $data) . '\\';
        }
    }

    /**
     * 构造函数，使用数组传参，直接给成员赋值
     *
     * @access public
     * @param  array $args name-value数组
     */
    public function __construct($args = [])
    {
        if ( ! empty($args))
        {
            foreach ($args as $k => $v)
            {
                $this->{$k} = $v;
            }
        }
        $this->init();
    }

    /**
     * 对象初始化操作，不破坏原有的构造方法
     */
    public function init()
    {
    }

    /**
     * 生成驼峰命名格式的变量名
     *
     * @param $str
     * @return string
     */
    final protected function __buildCamelizeName($str)
    {
        return Inflector::classify($str);
    }

    /**
     * 返回对象属性，实现getter功能
     *
     * @access public
     * @param  string $name 属性名
     * @return mixed 指定的属性值
     * @throws UnknownPropertyException 属性值不存在的话
     * @throws InvalidCallException 属性值只读
     */
    public function __get($name)
    {
        // 默认的getter方法
        $method = 'get' . $this->__buildCamelizeName($name);
        if (method_exists($this, $method))
        {
            return $this->$method();
        }

        // 有可能只需要返回布尔值
        $method = 'is' . $this->__buildCamelizeName($name);
        if (method_exists($this, $method))
        {
            return (bool) $this->$method();
        }

        $method = 'set' . $this->__buildCamelizeName($name);
        if (method_exists($this, $method))
        {
            throw new InvalidCallException('Getting write-only property: {class}::{name}', [
                '{class}' => get_class($this),
                '{name}'  => $name,
            ]);
        }
        else
        {
            throw new InvalidCallException('Getting unknown property: {class}::{name}', [
                '{class}' => get_class($this),
                '{name}'  => $name,
            ]);
        }
    }

    /**
     * setter功能的实现
     *
     * @access public
     * @param  string $name  属性名
     * @param  mixed  $value 属性值
     * @throws UnknownPropertyException 属性值不存在
     * @throws InvalidCallException 属性值只读
     * @see    __get()
     */
    public function __set($name, $value)
    {
        $method = 'set' . $this->__buildCamelizeName($name);
        if (method_exists($this, $method))
        {
            $this->$method($value);
            return;
        }

        $method = 'get' . $this->__buildCamelizeName($name);
        if (method_exists($this, $method))
        {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        }
        else
        {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * 检查setter是否存在
     *
     * @param string $name 属性名
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter))
        {
            return null !== $this->$getter();
        }
        else
        {
            return false;
        }
    }

    /**
     * 注销值
     *
     * @param string $name 属性名
     * @throws InvalidCallException  属性值只读的话
     */
    public function __unset($name)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter))
        {
            $this->$setter(null);
        }
        elseif (method_exists($this, 'get' . ucfirst($name)))
        {
            throw new InvalidCallException('Deleting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * 将指定的属性转为布尔值
     *
     * @param $property
     * @param $value
     * @throws BaseException
     */
    public function toBoolean($property, $value)
    {
        $trueValues = [true, 1, '1', 'true', 'yes', 'on'];
        $falseValues = [false, 0, '0', 'false', 'no', 'off', ''];

        if (is_string($value))
        {
            $value = strtolower($value);
        }

        $result = null;
        if ($result === null && in_array($value, $trueValues, true))
        {
            $result = true;
        }
        if ($result === null && in_array($value, $falseValues, true))
        {
            $result = false;
        }

        // 强制转换
        try
        {
            $this->$property = (bool) $result;
        }
        catch (Exception $ex)
        {
            throw new BaseException('Property :property is not existed or the value is cast.', [
                ':property' => $property,
            ]);
        }
    }

    /**
     * 转换指定字段数据为整形
     *
     * @param $property
     * @param $value
     * @throws BaseException
     */
    public function toInteger($property, $value)
    {
        $result = 0;

        if (is_integer($value))
        {
            $result = $value;
        }
        if (is_numeric($value) && ($value == (integer) $value))
        {
            $result = (integer) $value;
        }

        try
        {
            $this->$property = (int) $result;
        }
        catch (Exception $e)
        {
            throw new BaseException('Property :property is not existed or the value is cast.', [
                ':property' => $property,
            ]);
        }
    }

    /**
     * 是否存在指定的属性
     *
     * @param  string $name      属性名
     * @param  bool   $checkVars 是否检查成员变量
     * @return bool
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * 检查指定的属性是否可读
     *
     * @param  string $name      属性名
     * @param  bool   $checkVars 是否检查成员变量
     * @return bool
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . ucfirst($name)) || $checkVars && property_exists($this, $name);
    }

    /**
     * 检查指定的属性是否可写
     *
     * @param string $name      属性名
     * @param bool   $checkVars 是否检查成员变量
     * @return bool
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . ucfirst($name)) || $checkVars && property_exists($this, $name);
    }

    /**
     * 检查对象是否有指定方法
     *
     * @param string $name 方法名
     * @return bool 方法是否定义
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}

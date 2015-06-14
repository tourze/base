<?php

namespace tourze\Model\Support;

use Doctrine\DBAL\Connection;
use tourze\Base\Helper\Arr;

trait Field
{

    /**
     * Auto-update columns for creation
     *
     * @var string
     */
    protected $_createdColumn = null;

    /**
     * @param null $createdColumn
     * @return $this|string
     */
    public function createdColumn($createdColumn = null)
    {
        if ($createdColumn === null)
        {
            return $this->_createdColumn;
        }

        $this->_createdColumn = $createdColumn;

        return $this;
    }

    /**
     * Auto-update columns for updates
     *
     * @var string
     */
    protected $_updatedColumn = null;

    /**
     * @param null $updatedColumn
     * @return mixed
     */
    public function updatedColumn($updatedColumn = null)
    {
        if ($updatedColumn === null)
        {
            return $this->_updatedColumn;
        }

        $this->_updatedColumn = $updatedColumn;

        return $this;
    }

    /**
     * @var array
     */
    protected $_changed = [];

    /**
     * 当前模型有改动过的数据信息
     *
     * @param string $field field to check for changes
     *
     * @return  bool  Whether or not the field has changed
     */
    public function changed($field = null)
    {
        return (null === $field)
            ? $this->_changed
            : Arr::get($this->_changed, $field);
    }

    /**
     * 扩展的label方法
     *
     * @param          $key
     * @param   mixed  $value
     * @return  $this
     */
    public function label($key, $value = null)
    {
        if ($value === null)
        {
            return isset($this->_labels[$key]) ? $this->_labels[$key] : $key;
        }

        $this->_labels[$key] = $value;
        return $this;
    }

    protected $_labels = [];

    /**
     * Label definitions for validation
     *
     * @param null $labels
     * @return array
     */
    public function labels($labels = null)
    {
        if ($labels === null)
        {
            return $this->_labels;
        }

        $this->_labels = $labels;
        return $this;
    }

    /**
     * 一对一关系
     *
     * @var array
     */
    protected $_hasOne = [];

    /**
     * 读取/设置一对一关系
     *
     * @param null $hasOne
     *
     * @return $this|array
     */
    public function hasOne($hasOne = null)
    {
        if ($hasOne === null)
        {
            return $this->_hasOne;
        }
        $this->_hasOne = $hasOne;

        return $this;
    }

    /**
     * 从属关系
     *
     * @var array
     */
    protected $_belongsTo = [];

    /**
     * 读取/设置从属关系
     *
     * @param null $belongsTo
     *
     * @return $this|array
     */
    public function belongsTo($belongsTo = null)
    {
        if ($belongsTo === null)
        {
            return $this->_belongsTo;
        }
        $this->_belongsTo = $belongsTo;

        return $this;
    }

    /**
     * 一对多关系
     *
     * @var array
     */
    public $_hasMany = [];

    /**
     * 读取/设置一对多关系
     *
     * @param null $hasMany
     *
     * @return $this|array
     */
    public function hasMany($hasMany = null)
    {
        if ($hasMany === null)
        {
            return $this->_hasMany;
        }
        $this->_hasMany = $hasMany;

        return $this;
    }

    /**
     * 自动加载的关系
     *
     * @var array
     */
    protected $_loadWith = [];

    /**
     * 读取/设置自动加载关系
     *
     * @param null $loadWith
     *
     * @return $this|array
     */
    public function loadWith($loadWith = null)
    {
        if ($loadWith === null)
        {
            return $this->_loadWith;
        }
        $this->_loadWith = $loadWith;

        return $this;
    }

    /**
     * 当前对象数据
     *
     * @var array
     */
    protected $_object = [];

    /**
     * 读取/设置对象数据
     *
     * @param null $object
     *
     * @return $this|array
     */
    public function object($object = null)
    {
        if ($object === null)
        {
            return $this->_object;
        }
        $this->_object = $object;

        return $this;
    }


    /**
     * 外键后缀
     *
     * @var string
     */
    protected $_foreignKeySuffix = '_id';

    /**
     * 读取/设置自动加载关系
     *
     * @param null $foreignKeySuffix
     *
     * @return $this|string
     */
    public function foreignKeySuffix($foreignKeySuffix = null)
    {
        if ($foreignKeySuffix === null)
        {
            return $this->_foreignKeySuffix;
        }
        $this->_foreignKeySuffix = $foreignKeySuffix;

        return $this;
    }

    /**
     * 当前对象名
     *
     * @var string
     */
    protected $_objectName;

    /**
     * 读取/设置自动加载关系
     *
     * @param null $objectName
     *
     * @return $this|string
     */
    public function objectName($objectName = null)
    {
        if ($objectName === null)
        {
            return $this->_objectName;
        }
        $this->_objectName = $objectName;

        return $this;
    }

    /**
     * 表名
     *
     * @var string
     */
    protected $_tableName;

    /**
     * 设置和读取表名
     *
     * @param $tableName
     *
     * @return $this|string
     */
    public function tableName($tableName = null)
    {
        if ($tableName === null)
        {
            return $this->_tableName;
        }
        $this->_tableName = $tableName;

        return $this;
    }

    /**
     * 字段数组
     *
     * @var array
     */
    protected $_tableColumns = null;

    /**
     * 设置和读取自动序列化/反序列化的字段
     *
     * @param $tableColumns
     *
     * @return $this|array
     */
    public function tableColumns($tableColumns = null)
    {
        if ($tableColumns === null)
        {
            return $this->_tableColumns;
        }
        $this->_tableColumns = $tableColumns;

        return $this;
    }

    /**
     * 自动序列化/反序列化的字段
     *
     * @var array
     */
    protected $_serializeColumns = [];

    /**
     * 设置和读取自动序列化/反序列化的字段
     *
     * @param $serializeColumns
     *
     * @return $this|array
     */
    public function serializeColumns($serializeColumns = null)
    {
        if ($serializeColumns === null)
        {
            return $this->_serializeColumns;
        }
        $this->_serializeColumns = $serializeColumns;

        return $this;
    }

    /**
     * 当前模型使用的数据库组
     *
     * @var String
     */
    protected $_dbGroup = null;

    /**
     * 设置和读取当前模型使用的数据库组
     *
     * @param $dbGroup
     *
     * @return $this|string
     */
    public function dbGroup($dbGroup = null)
    {
        if ($dbGroup === null)
        {
            return $this->_dbGroup;
        }
        $this->_dbGroup = $dbGroup;

        return $this;
    }

    /**
     * 当前使用的数据库对象
     *
     * @var Connection
     */
    protected $_db = null;

    /**
     * 设置和读取当前模型使用的数据库访问实例
     *
     * @param Connection $db
     *
     * @return $this|Connection
     */
    public function db($db = null)
    {
        if ($db === null)
        {
            return $this->_db;
        }
        $this->_db = $db;

        return $this;
    }

    /**
     * The message filename used for validation errors.
     * Defaults to self::$_objectName
     *
     * @var string
     */
    protected $_errorFileName = null;

    /**
     * 设置和读取错误异常文本文件
     *
     * @param $errorFileName
     * @return $this|array
     */
    public function errorFileName($errorFileName = null)
    {
        if ($errorFileName === null)
        {
            return $this->_errorFileName;
        }
        $this->_errorFileName = $errorFileName;

        return $this;
    }

    protected $_asObject = false;

    /**
     * 设置和读取错误异常文本文件
     *
     * @param $asObject
     * @return $this|mixed
     */
    public function asObject($asObject = null)
    {
        if ($asObject === null)
        {
            return $this->_asObject;
        }
        $this->_asObject = $asObject;

        return $this;
    }

    /**
     * 序列化数据
     *
     * @param $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        return json_encode($value);
    }

    /**
     * 反序列数据
     *
     * @param $value
     * @return mixed
     */
    protected function _unserializeValue($value)
    {
        return json_decode($value, true);
    }
}

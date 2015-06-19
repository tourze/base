<?php

namespace tourze\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use PDO;
use ReflectionFunction;
use ReflectionMethod;
use tourze\Base\Db;
use tourze\Base\Exception\InvalidCallException;
use tourze\Base\Object;
use tourze\Base\Helper\Arr;
use tourze\Model\Exception\ColumnNotFoundException;
use tourze\Model\Exception\ModelException;
use tourze\Model\Exception\ValidationException;
use tourze\Model\Support\Dbal;
use tourze\Model\Support\Finder;
use tourze\Base\Security\Validation;
use serializable;

/**
 * AR模型的ORM
 *
 * @package    Base/ORM
 * @author     YwiSax
 */
class Model extends Object implements serializable, Finder
{

    use Dbal;

    /**
     * 保存字段缓存
     *
     * @var array
     */
    public static $_columnCache = [];

    /**
     * 在更新或者创建记录前进行校验
     *
     * @var  Validation
     */
    protected $_validation = null;

    /**
     * 对模型进行校验
     *
     * @param  Validation $validation
     * @return Validation|$this
     */
    public function validation(Validation $validation = null)
    {
        if (null === $validation)
        {
            if ( ! $this->_validation)
            {
                $this->_validation();
            }

            return $this->_validation;
        }

        $this->_validation = $validation;
        return $this;
    }

    /**
     * 对当前数据进行校验
     *
     * @return void
     */
    protected function _validation()
    {
        // 绑定一些必要的变量
        $this->_validation = Validation::factory($this->_fullObjectData())
            ->bind(':model', $this)
            ->bind(':originalValues', $this->_originalValues)
            ->bind(':changed', $this->_changed);

        $this->_validation->setErrorFileName($this->errorFileName());

        foreach ($this->rules() as $field => $rules)
        {
            $this->_validation->rules($field, $rules);
        }

        // Use column names by default for labels
        $columns = array_keys($this->_tableColumns);

        // Merge user-defined labels
        $labels = array_merge(array_combine($columns, $columns), $this->labels());

        foreach ($labels as $field => $label)
        {
            $this->_validation->label($field, $label);
        }
    }

    protected function _fullObjectData()
    {
        $object = $this->_object;

        $properties = get_object_vars($this);
        foreach ($properties as $k => $v)
        {
            if ($k{0} != '_')
            {
                $object[$k] = $v;
            }
        }

        return $object;
    }

    /**
     * @var array
     */
    protected $_originalValues = [];

    /**
     * @var array
     */
    protected $_related = [];

    /**
     * @var bool
     */
    protected $_valid = false;

    /**
     * @var bool
     */
    protected $_loaded = false;

    /**
     * @var bool
     */
    protected $_saved = false;

    /**
     * @var array
     */
    protected $_sorting;

    /**
     * Table primary key
     *
     * @var string
     */
    protected $_primaryKey = 'id';

    /**
     * Primary key value
     *
     * @var mixed
     */
    protected $_primaryKeyValue;

    /**
     * Model configuration, reload on wakeup?
     *
     * @var bool
     */
    protected $_reloadOnWakeup = true;

    /**
     * Database methods applied
     *
     * @var array
     */
    protected $_dbApplied = [];

    /**
     * Reset builder
     *
     * @var bool
     */
    protected $_dbReset = true;

    /**
     * Database query builder
     *
     * @var QueryBuilder
     */
    protected $_dbBuilder;

    /**
     * With calls already applied
     *
     * @var array
     */
    protected $_withApplied = [];

    /**
     * @var array  Data to be loaded into the model from a database call cast
     */
    protected $_castData = [];

    /**
     * ORM的构造方法，可以传参来直接加载模型数据
     *
     * @param  mixed $id 查询参数
     */
    public function __construct($id = null)
    {
        parent::__construct();

        if (null !== $id)
        {
            if (is_array($id))
            {
                foreach ($id as $column => $value)
                {
                    $this->where($column, '=', $value);
                }
                $this->find();
            }
            else
            {
                $this->where($this->_objectName . '.' . $this->_primaryKey, '=', $id)->find();
            }
        }
        elseif ( ! empty($this->_castData))
        {
            // Load preloaded data from a database call cast
            $this->_loadValues($this->_castData);

            $this->_castData = [];
        }
    }

    /**
     * 准备和初始化数据
     */
    public function init()
    {
        // 设置对象名
        if ( ! $this->objectName())
        {
            $this->objectName($this->tableName());
        }

        // 错误文件
        if ( ! $this->errorFileName())
        {
            $this->errorFileName(str_replace('_', DIRECTORY_SEPARATOR, strtolower($this->objectName())));
        }

        // 设置对象名
        if ( ! is_object($this->db()))
        {
            $this->db(Db::instance($this->dbGroup()));
        }

        // BELONGS TO 关系
        $belongsTo = [];
        $defaults = [];
        foreach ($this->belongsTo() as $alias => $details)
        {
            if ( ! isset($details['model']))
            {
                $defaults['model'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $alias)));
            }

            $defaults['foreignKey'] = $alias . $this->foreignKeySuffix();

            $belongsTo[$alias] = array_merge($defaults, $details);
        }
        $this->belongsTo($belongsTo);

        // HAS ONE 关系
        $hasOne = [];
        $defaults = [];
        foreach ($this->hasOne() as $alias => $details)
        {
            if ( ! isset($details['model']))
            {
                $defaults['model'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $alias)));
            }

            $defaults['foreignKey'] = $this->objectName() . $this->foreignKeySuffix();

            $hasOne[$alias] = array_merge($defaults, $details);
        }
        $this->hasOne($hasOne);

        // HAS MANY
        $hasMany = [];
        $defaults = [];
        foreach ($this->hasMany() as $alias => $details)
        {
            if ( ! isset($details['model']))
            {
                $defaults['model'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $alias)));
            }

            $defaults['foreignKey'] = $this->objectName() . $this->foreignKeySuffix();
            $defaults['through'] = null;

            if ( ! isset($details['farKey']))
            {
                $defaults['farKey'] = $alias . $this->foreignKeySuffix();
            }

            $hasMany[$alias] = array_merge($defaults, $details);
        }
        $this->hasMany($hasMany);

        $objectName = $this->objectName();

        // 加载字段数据
        if (null === $this->tableColumns())
        {
            if (isset(self::$_columnCache[$objectName]))
            {
                // 尝试从缓存中加载
                $tableColumns = self::$_columnCache[$objectName];
            }
            else
            {
                // 尝试从数据库中加载
                $tableColumns = $this->listColumns();
                // 写入缓存
                self::$_columnCache[$objectName] = $tableColumns;
            }

            if ( ! $tableColumns)
            {
                throw new ModelException('Model columns should not be empty.');
            }

            $this->tableColumns($tableColumns);
        }

        $this->clear();
    }

    /**
     * 清空当前对象的数据
     *
     * @return $this
     */
    public function clear()
    {
        // 创建一个包含所有字段的数组，并且赋值为null
        $values = array_combine(array_keys($this->_tableColumns), array_fill(0, count($this->_tableColumns), null));

        // Replace the object and reset the object status
        $this->_object = $this->_changed = $this->_related = $this->_originalValues = [];

        // Replace the current object with an empty one
        $this->_loadValues($values);

        // 重置主键
        $this->_primaryKeyValue = null;

        // 重置加载状态
        $this->_loaded = false;

        $this->reset();
        return $this;
    }

    /**
     * Reloads the current object from the database.
     *
     * @return $this
     */
    public function reload()
    {
        $primaryKey = $this->pk();

        // Replace the object and reset the object status
        $this->_object = $this->_changed = $this->_related = $this->_originalValues = [];

        // Only reload the object if we have one to reload
        if ($this->_loaded)
        {
            return $this->clear()
                ->where($this->_objectName . '.' . $this->_primaryKey, '=', $primaryKey)
                ->find();
        }
        else
        {
            return $this->clear();
        }
    }

    /**
     * Checks if object data is set.
     *
     * @param  string $column Column name
     *
     * @return boolean
     */
    public function __isset($column)
    {
        return (isset($this->_object[$column]) OR
            isset($this->_related[$column]) OR
            isset($this->_hasOne[$column]) OR
            isset($this->_belongsTo[$column]) OR
            isset($this->_hasMany[$column]));
    }

    /**
     * 注销指定对象的数据
     *
     * @param  string $column Column name
     *
     * @return void
     */
    public function __unset($column)
    {
        unset($this->_object[$column], $this->_changed[$column], $this->_related[$column]);
    }

    /**
     * Displays the primary key of a model when it is converted to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->pk();
    }

    /**
     * 序列化数据，保存状态信息
     *
     * @return string
     */
    public function serialize()
    {
        $data = [];

        // 保存重要的字段
        foreach (
            [
                '_primaryKeyValue',
                '_object',
                '_changed',
                '_loaded',
                '_saved',
                '_sorting',
                '_originalValues'
            ] as $var
        )
        {
            $data[$var] = $this->{$var};
        }

        return serialize($data);
    }

    /**
     * 重载对象和数据
     *
     * @param string $data String for unserialization
     * @return  void
     */
    public function unserialize($data)
    {
        $this->init();

        foreach (unserialize($data) as $name => $var)
        {
            $this->{$name} = $var;
        }

        if (true === $this->_reloadOnWakeup)
        {
            $this->reload();
        }
    }

    /**
     * Handles retrieval of all model values, relationships, and metadata.
     *
     * [!!] 一般情况下，不用覆盖整个方法
     *
     * @param   string $name Column name
     *
     * @return  mixed
     */
    public function __get($name)
    {
        try
        {
            return parent::__get($name);
        }
        catch (InvalidCallException $e)
        {
            return $this->get($name);
        }
    }

    /**
     * Handles getting of column
     * Override this method to add custom get behavior
     *
     * @param   string $column Column name
     * @return mixed
     * @throws ModelException
     */
    public function get($column)
    {
        // 如果是对象数据，那就直接返回
        if (array_key_exists($column, $this->_object))
        {
            return (in_array($column, $this->_serializeColumns))
                ? $this->_unserializeValue($this->_object[$column])
                : $this->_object[$column];
        }
        elseif (isset($this->_related[$column]))
        {
            // Return related model that has already been fetched
            return $this->_related[$column];
        }
        elseif (isset($this->_belongsTo[$column]))
        {
            $model = $this->_related($column);

            // Use this model's column and foreign model's primary key
            $col = $model->_objectName . '.' . $model->_primaryKey;
            $val = $this->_object[$this->_belongsTo[$column]['foreignKey']];

            // Make sure we don't run WHERE "AUTO_INCREMENT column" = null queries. This would
            // return the last inserted record instead of an empty result.
            // See: http://mysql.localhost.net.ar/doc/refman/5.1/en/server-session-variables.html#sysvar_sql_auto_is_null
            if (null !== $val)
            {
                $model
                    ->where($col, '=', $val)
                    ->find();
            }

            return $this->_related[$column] = $model;
        }
        elseif (isset($this->_hasOne[$column]))
        {
            $model = $this->_related($column);

            if (isset($this->_hasOne[$column]['through']))
            {
                // Grab hasMany "through" relationship table
                $through = $this->_hasOne[$column]['through'];

                // Join on through model's target foreign key (farKey) and target model's primary key
                $joinCol1 = $through . '.' . $this->_hasOne[$column]['farKey'];
                $joinCol2 = $model->_objectName . '.' . $model->_primaryKey;

                $model->leftJoin($this->objectName(), $through, $through, "$joinCol1 = $joinCol2");

                if (isset($this->_hasOne[$column]['throughColumns']))
                {
                    foreach ($this->_hasOne[$column]['throughColumns'] as $col)
                    {
                        $model->select($through . '.' . $col);
                    }
                }

                // Through table's source foreign key (foreignKey) should be this model's primary key
                $col = $through . '.' . $this->_hasOne[$column]['foreignKey'];
                $val = $this->pk();
            }
            else
            {
                // Use this model's primary key value and foreign model's column
                $col = $model->_objectName . '.' . $this->_hasOne[$column]['foreignKey'];
                $val = $this->pk();
            }

            $model
                ->where($col, '=', $val)
                ->find();

            return $this->_related[$column] = $model;
        }
        elseif (isset($this->_hasMany[$column]))
        {
            $modelClass = $this->_hasMany[$column]['model'];
            /** @var Model $model */
            $model = new $modelClass;

            if (isset($this->_hasMany[$column]['through']))
            {
                // Grab hasMany "through" relationship table
                $through = $this->_hasMany[$column]['through'];

                // Join on through model's target foreign key (farKey) and target model's primary key
                $joinCol1 = $through . '.' . $this->_hasMany[$column]['farKey'];
                $joinCol2 = $model->_objectName . '.' . $model->_primaryKey;

                $model->join($this->objectName(), $through, $through, "$joinCol1 = $joinCol2");

                if (isset($this->_hasMany[$column]['throughColumns']))
                {
                    foreach ($this->_hasMany[$column]['throughColumns'] as $col)
                    {
                        $model->select($through . '.' . $col);
                    }
                }

                // Through table's source foreign key (foreignKey) should be this model's primary key
                $col = $through . '.' . $this->_hasMany[$column]['foreignKey'];
                $val = $this->pk();
            }
            else
            {
                // Simple hasMany relationship, search where target model's foreign key is this model's primary key
                $col = $model->_objectName . '.' . $this->_hasMany[$column]['foreignKey'];
                $val = $this->pk();
            }

            return $model->where($col, '=', $val);
        }
        else
        {
            throw new ColumnNotFoundException('The :property property does not exist in the :class class', [
                ':property' => $column,
                ':class'    => self::className(),
            ]);
        }
    }

    /**
     * Base set method.
     * [!!] This should not be overridden.
     *
     * @param  string $column Column name
     * @param  mixed  $value  Column value
     *
     * @return void
     */
    public function __set($column, $value)
    {
        $this->set($column, $value);
    }

    /**
     * Handles setting of columns
     * Override this method to add custom set behavior
     *
     * @param  string $column Column name
     * @param  mixed  $value  Column value
     * @return $this
     * @throws ModelException
     */
    public function set($column, $value)
    {
        // 未加载对象？
        if ( ! $this->objectName())
        {
            $this->_castData[$column] = $value;
            return $this;
        }

        if (in_array($column, $this->serializeColumns()))
        {
            $value = $this->_serializeValue($value);
        }

        if (array_key_exists($column, $this->_object))
        {
            // Filter the data
            $value = $this->runFilter($column, $value);

            // See if the data really changed
            if ($value !== $this->_object[$column])
            {
                $this->_object[$column] = $value;

                // Data has changed
                $this->_changed[$column] = $column;

                // Object is no longer saved or valid
                $this->_saved = $this->_valid = false;
            }
        }
        elseif (isset($this->_belongsTo[$column]))
        {
            // Update related object itself
            $this->_related[$column] = $value;

            // Update the foreign key of this model
            $this->_object[$this->_belongsTo[$column]['foreignKey']] = ($value instanceof Model)
                ? $value->pk()
                : null;

            $this->_changed[$column] = $this->_belongsTo[$column]['foreignKey'];
        }
        else
        {
            throw new ModelException('The :property property does not exist in the :class class', [
                ':property' => $column,
                ':class'    => self::className()
            ]);
        }

        return $this;
    }

    /**
     * 用于加载数据的方法
     *
     * @param  array $values   Array of column => val
     * @param  array $expected Array of keys to take from $values
     *
     * @return Model
     */
    public function values(array $values, array $expected = null)
    {
        $properties = get_object_vars($this);
        foreach ($values as $k => $v)
        {
            if ($k{0} != '_' && array_key_exists($k, $properties))
            {
                $this->$k = $v;
            }
        }

        // 默认只导入跟模型相关字段
        if (null === $expected)
        {
            $expected = array_keys($this->_tableColumns);
            // 默认情况下主键不给自定义
            unset($values[$this->_primaryKey]);
        }

        foreach ($expected as $key => $column)
        {
            if (is_string($key))
            {
                // 注意不要用isset
                if ( ! array_key_exists($key, $values))
                {
                    continue;
                }

                // Try to set values to a related model
                $this->{$key}->values($values[$key], $column);
            }
            else
            {
                // 注意不要用isset
                if ( ! array_key_exists($column, $values))
                {
                    continue;
                }

                // Update the column, respects __set()
                $this->$column = $values[$column];
            }
        }

        return $this;
    }

    /**
     * @var array 可以转换为数组的字段
     */
    protected $_asArrayAttributes = [
        '_object',
        '_related'
    ];

    /**
     * @var array 转换为数组过程中，自动跳过的字段
     */
    protected $_asArrayIgnoreColumns = [];

    /**
     * 返回当前对象的数据（数组格式）
     *
     * @return array
     */
    public function asArray()
    {
        $array = [];

        $columns = $this->tableColumns();

        // 主要的属性
        foreach ($this->_asArrayAttributes as $attribute)
        {
            if (isset($this->{$attribute}))
            {
                foreach ($this->{$attribute} as $column => $value)
                {
                    if (in_array($column, $this->_asArrayIgnoreColumns))
                    {
                        continue;
                    }

                    // 关联数据
                    if ($value instanceof Model)
                    {
                        /** @var Model $model */
                        $array[$column] = $value->asArray();
                    }
                    // 普通数据
                    else
                    {
                        $columnData = $this->get($column);
                        // 根据DBAL返回的数据类型，进行一次转换
                        if (isset($columns[$column]))
                        {
                            $columnObject = $columns[$column];
                            if ($columnObject instanceof Column)
                            {
                                switch ($columnObject->getType()->getName())
                                {
                                    case 'bigint':
                                        $columnData = (int) $columnData;
                                        break;
                                    case 'integer':
                                        $columnData = (int) $columnData;
                                        break;
                                    case 'float':
                                        $columnData = (float) $columnData;
                                        break;
                                    default:
                                        //
                                }
                            }
                        }
                        $array[$column] = $columnData;
                    }
                }
            }
        }

        // 额外处理hasMany关系
        foreach ($this->hasMany() as $column => $relationConfig)
        {
            if (in_array($column, $this->_asArrayIgnoreColumns))
            {
                continue;
            }
            if (isset($relationConfig['as_array']) && $relationConfig['as_array'])
            {
                $array[$column] = [];
                $relationRecords = $this->{$column}->findAll();
                foreach ($relationRecords as $relationRecord)
                {
                    /** @var Model $relationRecord */
                    if (is_string($relationConfig['as_array']))
                    {
                        $relationRecord = $relationRecord->asArray();
                        $key = $relationRecord[$relationConfig['as_array']];
                        $array[$column][$key] = $relationRecord;
                    }
                    else
                    {
                        $array[$column][] = $relationRecord->asArray();
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Binds another one-to-one object to this model.  One-to-one objects
     * can be nested using 'object1:object2' syntax
     *
     * @param  string $targetPath Target model to bind to
     *
     * @return Model
     */
    public function with($targetPath)
    {
        if (isset($this->_withApplied[$targetPath]))
        {
            // Don't join anything already joined
            return $this;
        }

        // Split object parts
        $aliases = explode(':', $targetPath);
        $target = $this;
        $alias = $parent = null;
        foreach ($aliases as $alias)
        {
            // Go down the line of objects to find the given target
            $parent = $target;
            $target = $parent->_related($alias);

            if ( ! $target)
            {
                // Can't find related object
                return $this;
            }
        }

        // Target alias is at the end
        $targetAlias = $alias;

        // Pop-off top alias to get the parent path (user:photo:tag becomes user:photo - the parent table prefix)
        array_pop($aliases);
        $parentPath = implode(':', $aliases);

        if (empty($parentPath))
        {
            // Use this table name itself for the parent path
            $parentPath = $this->_objectName;
        }
        else
        {
            if ( ! isset($this->_withApplied[$parentPath]))
            {
                // If the parent path hasn't been joined yet, do it first (otherwise LEFT JOINs fail)
                $this->with($parentPath);
            }
        }

        // Add to with_applied to prevent duplicate joins
        $this->_withApplied[$targetPath] = true;

        // Use the keys of the empty object to determine the columns
        foreach (array_keys($target->_object) as $column)
        {
            $name = $targetPath . '.' . $column;
            $alias = $targetPath . ':' . $column;

            // Add the prefix so that load_result can determine the relationship
            $this->select([$name, $alias]);
        }

        if (isset($parent->_belongsTo[$targetAlias]))
        {
            // Parent belongs to target, use target's primary key and parent's foreign key
            $joinCol1 = $targetPath . '.' . $target->_primaryKey;
            $joinCol2 = $parentPath . '.' . $parent->_belongsTo[$targetAlias]['foreignKey'];
        }
        else
        {
            // Parent has_one target, use parent's primary key as target's foreign key
            $joinCol1 = $parentPath . '.' . $parent->_primaryKey;
            $joinCol2 = $targetPath . '.' . $parent->_hasOne[$targetAlias]['foreignKey'];
        }

        // Join the related object into the result
        $this->leftJoin($this->objectName(), $target->_tableName, $targetPath, "$joinCol1 = $joinCol2");

        return $this;
    }

    /**
     * 查找单条记录
     *
     * @param null $conditions
     *
     * @return Model
     * @throws ModelException
     */
    public function find($conditions = null)
    {
        // 已经加载，忽略
        if ($this->_loaded)
        {
            throw new ModelException('Method find() cannot be called on loaded objects');
        }

        if (is_array($conditions))
        {
            foreach ($conditions as $k => $v)
            {
                $this->where($k, '=', $v);
            }
        }
        elseif ($conditions !== null)
        {
            $this->where($this->primaryKey(), '=', $conditions);
        }

        if ( ! empty($this->_loadWith))
        {
            foreach ($this->_loadWith as $alias)
            {
                // Bind auto relationships
                $this->with($alias);
            }
        }

        $this->_build(Db::SELECT);

        return $this->_loadResult(false);
    }

    /**
     * 查找多条记录
     *
     * @param null $conditions
     *
     * @return mixed
     * @throws ModelException
     */
    public function findAll($conditions = null)
    {
        if ($this->_loaded)
        {
            throw new ModelException('Method findAll() cannot be called on loaded objects');
        }

        if (is_array($conditions))
        {
            foreach ($conditions as $k => $v)
            {
                $this->where($k, '=', $v);
            }
        }
        elseif ($conditions !== null)
        {
            $this->where($this->primaryKey(), '=', $conditions);
        }

        if ( ! empty($this->_loadWith))
        {
            foreach ($this->_loadWith as $alias)
            {
                // Bind auto relationships
                $this->with($alias);
            }
        }

        $this->_build(Db::SELECT);

        return $this->_loadResult(true);
    }

    /**
     * Loads an array of values into into the current object.
     *
     * @param  array $values Values to load
     *
     * @return Model
     */
    protected function _loadValues(array $values)
    {
        if (array_key_exists($this->_primaryKey, $values))
        {
            if (null !== $values[$this->_primaryKey])
            {
                // Flag as loaded and valid
                $this->_loaded = $this->_valid = true;

                // Store primary key
                $this->_primaryKeyValue = $values[$this->_primaryKey];
            }
            else
            {
                // Not loaded or valid
                $this->_loaded = $this->_valid = false;
            }
        }

        // Related objects
        $related = [];

        foreach ($values as $column => $value)
        {
            if (false === strpos($column, ':'))
            {
                // Load the value to this model
                $this->_object[$column] = $value;
            }
            else
            {
                // Column belongs to a related model
                list ($prefix, $column) = explode(':', $column, 2);

                $related[$prefix][$column] = $value;
            }
        }

        if ( ! empty($related))
        {
            foreach ($related as $object => $values)
            {
                // Load the related objects with the values in the result
                $this->_related($object)
                    ->_loadValues($values);
            }
        }

        if ($this->_loaded)
        {
            // Store the object in its original state
            $this->_originalValues = $this->_object;
        }

        return $this;
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    protected function runFilterCallback($field, $value, $array, $bound = null)
    {
        if (null === $bound)
        {
            // Bind the field name and model so they can be used in the filter method
            $bound = [
                ':field' => $field,
                ':model' => $this,
            ];
            $bound[':value'] = $value;
        }

        // Filters are defined as [$filter, $params]
        $filter = $array[0];
        $params = Arr::get($array, 1, [':value']);

        foreach ($params as $key => $param)
        {
            if (is_string($param) && array_key_exists($param, $bound))
            {
                // Replace with bound value
                $params[$key] = $bound[$param];
            }
        }

        if (method_exists('\\tourze\\Model\\Feature\\Filter', $filter))
        {
            $filter = '\\tourze\\Model\\Feature\\Filter::' . $filter;
        }

        // 如果filter是数组，那么直接执行
        if (is_array($filter) || ! is_string($filter))
        {
            // This is either a callback as an array or a lambda
            $value = call_user_func_array($filter, $params);
        }
        // 不带::，即为普通函数
        elseif (false === strpos($filter, '::'))
        {
            // Use a function call
            $function = new ReflectionFunction($filter);

            // Call $function($this[$field], $param, ...) with Reflection
            //echo $field.'~'.$filter;echo "\n";
            //print_r($params);
            $value = $function->invokeArgs($params);
        }
        else
        {
            // Split the class and method of the rule
            list($class, $method) = explode('::', $filter, 2);

            // Use a static method call
            $method = new ReflectionMethod($class, $method);

            // Call $Class::$method($this[$field], $param, ...) with Reflection
            $value = $method->invokeArgs(null, $params);
        }

        return $value;
    }

    /**
     * Filters a value for a specific column
     *
     * @param  string $field The column name
     * @param  string $value The value to filter
     *
     * @return string
     */
    protected function runFilter($field, $value)
    {
        $filters = $this->filters();

        // Get the filters for this column
        $wildcards = empty($filters[true]) ? [] : $filters[true];

        // Merge in the wildcards
        $filters = empty($filters[$field]) ? $wildcards : array_merge($wildcards, $filters[$field]);

        // Bind the field name and model so they can be used in the filter method
        $bound = [
            ':field' => $field,
            ':model' => $this,
        ];

        foreach ($filters as $array)
        {
            // Value needs to be bound inside the loop so we are always using the
            // version that was modified by the filters that already ran
            $bound[':value'] = $value;
            $value = $this->runFilterCallback($field, $value, $array, $bound);
        }

        return $value;
    }

    /**
     * Filter definitions for validation
     *
     * @return array
     */
    public function filters()
    {
        return [];
    }

    /**
     * 检查当前对象的数据是否合法
     *
     * @param  Validation $extraValidation Validation object
     * @throws ValidationException
     * @return Model
     */
    public function check(Validation $extraValidation = null)
    {
        // Determine if any external validation failed
        $extraErrors = ($extraValidation && ! $extraValidation->check());

        // Always build a new validation object
        $this->_validation();

        $array = $this->_validation;

        if (false === ($this->_valid = $array->check()) || $extraErrors)
        {
            $exception = new ValidationException($this->errorFileName(), $array);
            if ($extraErrors)
            {
                // 合并附加的验证规则
                $exception->addObject('_external', $extraValidation);
            }
            throw $exception;
        }

        return $this;
    }

    /**
     * Insert a new object to the database
     *
     * @param  Validation $validation Validation object
     *
     * @throws ModelException
     * @return Model
     */
    public function create(Validation $validation = null)
    {
        if ($this->_loaded)
        {
            throw new ModelException('Cannot create :model model because it is already loaded.', [
                ':model' => $this->_objectName
            ]);
        }

        // Require model validation before saving
        if ( ! $this->_valid || $validation)
        {
            $this->check($validation);
        }

        $data = [];
        foreach ($this->_changed as $column)
        {
            // Generate list of column => values
            $data[$column] = $this->_object[$column];
        }

        // 自动更新字段
        if (is_array($createdColumn = $this->createdColumn()))
        {
            $column = Arr::get($createdColumn, 'column');
            $format = Arr::get($createdColumn, 'format');

            $data[$column] = $this->_object[$column] = (true === $format) ? time() : date($format);
        }

        $query = $this->db()->createQueryBuilder();
        $query->insert($this->tableName());
        foreach ($data as $k => $v)
        {
            $query->setValue($k, ":$k");
            $query->setParameter($k, $v);
        }

        $result = $query->execute();

        if ( ! array_key_exists($this->_primaryKey, $data))
        {
            // Load the insert id as the primary key if it was left out
            $this->_object[$this->_primaryKey] = $this->_primaryKeyValue = $result[0];
        }
        else
        {
            $this->_primaryKeyValue = $this->_object[$this->_primaryKey];
        }

        // Object is now loaded and saved
        $this->_loaded = $this->_saved = true;

        // All changes have been saved
        $this->_changed = [];
        $this->_originalValues = $this->_object;

        return $this;
    }

    /**
     * Updates a single record or multiple records
     *
     * @param  Validation $validation Validation object
     *
     * @throws ModelException
     * @return Model
     */
    public function update(Validation $validation = null)
    {
        if ( ! $this->_loaded)
        {
            throw new ModelException('Cannot update :model model because it is not loaded.', [
                ':model' => $this->_objectName
            ]);
        }

        // Run validation if the model isn't valid or we have additional validation rules.
        if ( ! $this->_valid || $validation)
        {
            $this->check($validation);
        }

        if (empty($this->_changed))
        {
            // Nothing to update
            return $this;
        }

        $data = [];
        foreach ($this->_changed as $column)
        {
            // Compile changed data
            $data[$column] = $this->_object[$column];
        }

        // 更新时，自动更新该字段
        if (is_array($updatedColumn = $this->updatedColumn()))
        {
            $column = Arr::get($updatedColumn, 'column');
            $format = Arr::get($updatedColumn, 'format');

            $data[$column] = $this->_object[$column] = (true === $format) ? time() : date($format);
        }

        $id = $this->pk();
        $query = $this->_db->createQueryBuilder()
            ->update($this->_tableName)
            ->where($this->_primaryKey.' = :id')
            ->setParameter(':id', $id);
        foreach ($data as $k => $v)
        {
            $query->set($k, ":$k");
            $query->setParameter($k, $v);
        }
        $query->execute();

        if (isset($data[$this->_primaryKey]))
        {
            // Primary key was changed, reflect it
            $this->_primaryKeyValue = $data[$this->_primaryKey];
        }

        // Object has been saved
        $this->_saved = true;

        // All changes have been saved
        $this->_changed = [];
        $this->_originalValues = $this->_object;

        return $this;
    }

    /**
     * Updates or Creates the record depending on loaded()
     *
     * @param  Validation $validation Validation object
     *
     * @return Model
     */
    public function save(Validation $validation = null)
    {
        $this->loaded()
            ? $this->update($validation)
            : $this->create($validation);

        return $this;
    }

    /**
     * Deletes a single record while ignoring relationships.
     *
     * @throws ModelException
     * @return Model
     */
    public function delete()
    {
        if ( ! $this->_loaded)
        {
            throw new ModelException('Cannot delete :model model because it is not loaded.', [
                ':model' => $this->_objectName
            ]);
        }

        $id = $this->pk();
        $this->_db->createQueryBuilder()
            ->delete($this->_tableName)
            ->where($this->_primaryKey . ' = ?')
            ->setParameter(0, $id)
            ->execute();

        return $this->clear();
    }

    /**
     * 获取当前做了修改的字段和对应数据
     */
    public function changedData()
    {
        $data = [];
        foreach ($this->_changed as $column)
        {
            $data[$column] = $this->_object[$column];
        }

        return $data;
    }

    /**
     * Tests if this object has a relationship to a different model,
     * or an array of different models. When providing far keys, the number
     * of relations must equal the number of keys.
     *     // Check if $model has the login role
     *     $model->has('roles', self::factory('role', ['name' => 'login']));
     *     // Check for the login role if you know the roles.id is 5
     *     $model->has('roles', 5);
     *     // Check for all of the following roles
     *     $model->has('roles', [1, 2, 3, 4]);
     *     // Check if $model has any roles
     *     $model->has('roles')
     *
     * @param  string $alias   Alias of the hasMany "through" relationship
     * @param  mixed  $farKeys Related model, primary key, or an array of primary keys
     *
     * @return boolean
     */
    public function has($alias, $farKeys = null)
    {
        $count = $this->countRelations($alias, $farKeys);
        if (null === $farKeys)
        {
            return (bool) $count;
        }
        else
        {
            return $count === count($farKeys);
        }
    }

    /**
     * Tests if this object has a relationship to a different model,
     * or an array of different models. When providing far keys, this function
     * only checks that at least one of the relationships is satisfied.
     *     // Check if $model has the login role
     *     $model->has('roles', self::factory('role', ['name' => 'login']));
     *     // Check for the login role if you know the roles.id is 5
     *     $model->has('roles', 5);
     *     // Check for any of the following roles
     *     $model->has('roles', [1, 2, 3, 4]);
     *     // Check if $model has any roles
     *     $model->has('roles')
     *
     * @param  string $alias   Alias of the hasMany "through" relationship
     * @param  mixed  $farKeys Related model, primary key, or an array of primary keys
     *
     * @return boolean
     */
    public function hasAny($alias, $farKeys = null)
    {
        return (bool) $this->countRelations($alias, $farKeys);
    }

    /**
     * Returns the number of relationships
     *     // Counts the number of times the login role is attached to $model
     *     $model->countRelations('roles', self::factory('role', ['name' => 'login']));
     *     // Counts the number of times role 5 is attached to $model
     *     $model->countRelations('roles', 5);
     *     // Counts the number of times any of roles 1, 2, 3, or 4 are attached to
     *     // $model
     *     $model->countRelations('roles', [1, 2, 3, 4]);
     *     // Counts the number roles attached to $model
     *     $model->countRelations('roles')
     *
     * @param  string $alias   Alias of the hasMany "through" relationship
     * @param  mixed  $farKeys Related model, primary key, or an array of primary keys
     *
     * @return integer
     */
    public function countRelations($alias, $farKeys = null)
    {
        if (null === $farKeys)
        {
            return $this->_db
                ->createQueryBuilder()
                ->select('COUNT(*) as records_found')
                ->from($this->_hasMany[$alias]['through'])
                ->where($this->_hasMany[$alias]['foreignKey'].' = :pk')
                ->setParameter('pk', $this->pk())
                ->execute()
                ->fetch();
        }

        $farKeys = ($farKeys instanceof Model) ? $farKeys->pk() : $farKeys;

        // We need an array to simplify the logic
        $farKeys = (array) $farKeys;

        // Nothing to check if the model isn't loaded or we don't have any farKey
        if ( ! $farKeys || ! $this->_loaded)
        {
            return 0;
        }

        $count = $this->_db->createQueryBuilder()
            ->select('COUNT(*) as records_found')
            ->from($this->_hasMany[$alias]['through'])
            ->where($this->_hasMany[$alias]['foreignKey'].' = :foreignKey')
            ->where($this->_hasMany[$alias]['farKey'].' IN (:farKeys)')
            ->setParameter('foreignKey', $this->pk())
            ->setParameter('farKeys', $farKeys, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetch();;

        // Rows found need to match the rows searched
        return (int) $count;
    }

    /**
     * Adds a new relationship to between this model and another.
     *     // Add the login role using a model instance
     *     $model->add('roles', self::factory('role', ['name' => 'login']));
     *     // Add the login role if you know the roles.id is 5
     *     $model->add('roles', 5);
     *     // Add multiple roles (for example, from checkboxes on a form)
     *     $model->add('roles', [1, 2, 3, 4]);
     *
     * @param  string $alias   Alias of the hasMany "through" relationship
     * @param  mixed  $farKeys Related model, primary key, or an array of primary keys
     *
     * @return Model
     */
    public function add($alias, $farKeys)
    {
        $farKeys = ($farKeys instanceof Model) ? $farKeys->pk() : $farKeys;
        $foreignKey = $this->pk();

        $this->_db->createQueryBuilder()
            ->insert($this->_hasMany[$alias]['through'])
            ->values([
                $this->_hasMany[$alias]['foreignKey'] => '?',
                $this->_hasMany[$alias]['farKey'] => '?',
            ])
            ->setParameter(0, $foreignKey)
            ->setParameter(1, $farKeys)
            ->execute();

        return $this;
    }

    /**
     * Removes a relationship between this model and another.
     *     // Remove a role using a model instance
     *     $model->remove('roles', self::factory('role', ['name' => 'login']));
     *     // Remove the role knowing the primary key
     *     $model->remove('roles', 5);
     *     // Remove multiple roles (for example, from checkboxes on a form)
     *     $model->remove('roles', [1, 2, 3, 4]);
     *     // Remove all related roles
     *     $model->remove('roles');
     *
     * @param  string $alias   Alias of the hasMany "through" relationship
     * @param  mixed  $farKeys Related model, primary key, or an array of primary keys
     *
     * @return Model
     */
    public function remove($alias, $farKeys = null)
    {
        $farKeys = ($farKeys instanceof Model) ? $farKeys->pk() : $farKeys;

        $query = $this->_db->createQueryBuilder()
            ->delete($this->_hasMany[$alias]['through'])
            ->where($this->_hasMany[$alias]['foreignKey'].' = ?')
            ->setParameter(0, $this->pk());

        if (null !== $farKeys)
        {
            // Remove all the relationships in the array
            $query->where($this->_hasMany[$alias]['farKey'] . ' IN (?)');
            $query->setParameter(1, (array) $farKeys, Connection::PARAM_INT_ARRAY);
        }

        $query->execute();

        return $this;
    }

    /**
     * 计算表中的记录总数
     *
     * @return integer
     */
    /**
     * Count the number of records in the table.
     *
     * @return integer
     */
    public function countAll()
    {
        $selects = [];

        foreach ($this->_dbPending as $key => $method)
        {
            if ($method['name'] == 'select')
            {
                // Ignore any selected columns for now
                $selects[] = $method;
                unset($this->_dbPending[$key]);
            }
        }

        if ( ! empty($this->_loadWith))
        {
            foreach ($this->_loadWith as $alias)
            {
                // Bind relationship
                $this->with($alias);
            }
        }

        $this->_build(Db::SELECT);

        $records = $this->_dbBuilder
            ->select('COUNT(*) as records_found')
            ->from($this->_tableName, $this->_objectName)
            ->execute()
            ->fetch();
        $records = $records['records_found'];

        // Add back in selected columns
        $this->_dbPending += $selects;

        $this->reset();

        // Return the total number of records in a table
        return (int) $records;
    }

    /**
     * 列出数据表的字段
     *
     * @return array
     * @throws \tourze\Model\Exception\ModelException
     */
    public function listColumns()
    {
        $schemaManager = $this
            ->db()
            ->getSchemaManager();

        $columns = $schemaManager->listTableColumns($this->_tableName);

        if (empty($columns))
        {
            throw new ModelException('The model has no fields.');
        }

        return $columns;
    }

    /**
     * Returns an ORM model for the given one-one related alias
     *
     * @param  string $alias Alias name
     *
     * @return Model
     */
    protected function _related($alias)
    {
        if (isset($this->_related[$alias]))
        {
            return $this->_related[$alias];
        }
        elseif (isset($this->_hasOne[$alias]))
        {
            $modelClass = $this->_hasOne[$alias]['model'];
            return $this->_related[$alias] = new $modelClass;
        }
        elseif (isset($this->_belongsTo[$alias]))
        {
            $modelClass = $this->_belongsTo[$alias]['model'];
            return $this->_related[$alias] = new $modelClass;
        }
        else
        {
            return false;
        }
    }

    /**
     * 返回当前的主键值
     *
     * @return mixed Primary key
     */
    public function pk()
    {
        return $this->_primaryKeyValue;
    }

    /**
     * Clears query builder.  Passing false is useful to keep the existing
     * query conditions for another query.
     *
     * @param bool $next Pass false to avoid resetting on the next call
     *
     * @return Model
     */
    public function reset($next = true)
    {
        if ($next && $this->_dbReset)
        {
            $this->_dbPending = [];
            $this->_dbApplied = [];
            $this->_dbBuilder = null;
            $this->_withApplied = [];
        }

        // Reset on the next call?
        $this->_dbReset = $next;

        return $this;
    }

    public function loaded()
    {
        return $this->_loaded;
    }

    public function saved()
    {
        return $this->_saved;
    }

    public function primaryKey()
    {
        return $this->_primaryKey;
    }

    public function originalValues()
    {
        return $this->_originalValues;
    }

    /**
     * 检查指定属性的值，是否为唯一值
     *
     * @param   string $field  检查的字段
     * @param   mixed  $value  要对比的值
     *
     * @return  bool    是否为唯一值
     */
    public function unique($field, $value)
    {
        $class = self::className();
        /** @var Model $model */
        $model = (new $class);
        $model->where($field, '=', $value)
            ->find();
        if ($this->loaded())
        {
            return ( ! ($model->loaded() && $model->pk() != $this->pk()));
        }

        return ( ! $model->loaded());
    }

    /**
     * Initializes the Database Builder to given query type
     *
     * @param  integer $type Type of Database query
     *
     * @return $this
     */
    protected function _build($type)
    {
        // Construct new builder object based on query type
        switch ($type)
        {
            case Db::SELECT:
                $this->_dbBuilder = $this->_db
                    ->createQueryBuilder()
                    ->select();
                break;
            case Db::UPDATE:
                $this->_dbBuilder = $this->_db
                    ->createQueryBuilder()
                    ->update($this->_tableName, $this->_objectName);
                break;
            case Db::DELETE:
                $this->_dbBuilder = $this->_db
                    ->createQueryBuilder()
                    ->delete($this->_tableName);
        }

        // Process pending database method calls
        foreach ($this->_dbPending as $method)
        {
            $name = $method['name'];
            $args = $method['args'];

            $this->_dbApplied[$name] = $name;

            call_user_func_array([
                $this->_dbBuilder,
                $name
            ], $args);
        }

        return $this;
    }

    /**
     * Returns an array of columns to include in the select query. This method
     * can be overridden to change the default select behavior.
     *
     * @return array Columns to select
     */
    protected function _buildSelect()
    {
        $columns = [];

        foreach ($this->_tableColumns as $column => $_)
        {
            $columns[] = $this->_objectName . '.' . $column.' as '.$column;
        }

        return $columns;
    }

    /**
     * Loads a database result, either as a new record for this model, or as
     * an iterator for multiple rows.
     *
     * @param  bool $multiple Return an iterator or load a single row
     *
     * @return $this|mixed
     */
    protected function _loadResult($multiple = false)
    {
        $this->_dbBuilder->from($this->_tableName, $this->_objectName);

        // 只获取单条记录
        if (false === $multiple)
        {
            $this->_dbBuilder->setMaxResults(1);
        }

        // 默认选择所有字段
        $this->_dbBuilder->select($this->_buildSelect());

        // 处理排序问题
        if ( ! isset($this->_dbApplied['orderBy']) && ! empty($this->_sorting))
        {
            foreach ($this->_sorting as $column => $direction)
            {
                if (false === strpos($column, '.'))
                {
                    // Sorting column for use in JOINs
                    $column = $this->_objectName . '.' . $column;
                }

                $this->_dbBuilder->orderBy($column, $direction);
            }
        }

        if (true === $multiple)
        {
            $result = $this->_dbBuilder->execute();

            $result->setFetchMode(
                PDO::FETCH_CLASS,
                $this->_loadMultiResultFetcherClass(),
                $this->_loadMultiResultFetcherConstructor()
            );

            $this->reset();

            return $result->fetchAll();
        }
        else
        {
            $result = $this->_dbBuilder
                ->execute()
                ->fetch();

            $this->reset();

            if ($result)
            {
                $this->_loadValues($result);
            }
            else
            {
                $this->clear();
            }

            return $this;
        }
    }
    protected function _loadMultiResultFetcherClass()
    {
        return $this->asObject() ? $this->asObject() : self::className();
    }
    protected function _loadMultiResultFetcherConstructor()
    {
        return [];
    }
}

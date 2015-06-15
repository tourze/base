<?php

namespace tourze\Model\Support;

/**
 * 链式调用的接口实现
 *
 * @package tourze\Model\Model\Support
 */
trait Dbal
{

    use Field;

    /**
     * Database methods pending
     *
     * @var array
     */
    protected $_dbPending = [];

    /**
     * Alias of andWhere()
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function where($column, $op, $value)
    {
        // 默认是当前对象的字段
        if (false === strpos($column, '.'))
        {
            $column = $this->_objectName . '.' . $column;
        }

        $variable = 'v'.md5($column . md5(serialize($value)));

        $this->_dbPending[] = [
            'name' => 'where',
            'args' => ["$column $op :$variable"],
        ];
        $this->_dbPending[] = [
            'name' => 'setParameter',
            'args' => [$variable, $value],
        ];

        return $this;
    }

    /**
     * Creates a new "AND WHERE" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function andWhere($column, $op, $value)
    {
        // 默认是当前对象的字段
        if (false === strpos($column, '.'))
        {
            $column = $this->_objectName . '.' . $column;
        }

        $variable = 'v'.md5($column . md5(serialize($value)));

        $this->_dbPending[] = [
            'name' => 'andWhere',
            'args' => ["$column $op :$variable"],
        ];
        $this->_dbPending[] = [
            'name' => 'setParameter',
            'args' => [$variable, $value],
        ];

        return $this;
    }

    /**
     * Creates a new "OR WHERE" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function orWhere($column, $op = null, $value = null)
    {
        // 默认是当前对象的字段
        if (false === strpos($column, '.'))
        {
            $column = $this->_objectName . '.' . $column;
        }

        if ($op === null)
        {
            $where = $column;

            $this->_dbPending[] = [
                'name' => 'orWhere',
                'args' => [$where],
            ];
        }
        else
        {
            $variable = 'v'.md5($column . md5(serialize($value)));

            $this->_dbPending[] = [
                'name' => 'orWhere',
                'args' => ["$column $op :$variable"],
            ];
            $this->_dbPending[] = [
                'name' => 'setParameter',
                'args' => [$variable, $value],
            ];
        }

        return $this;
    }

    /**
     * Alias of andWhereOpen()
     *
     * @return  $this
     */
    public function whereOpen()
    {
        return $this->andWhereOpen();
    }

    /**
     * Opens a new "AND WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function andWhereOpen()
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'andWhereOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Opens a new "OR WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function orWhereOpen()
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'orWhereOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function whereClose()
    {
        return $this->andWhereClose();
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function andWhereClose()
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'andWhereClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "OR WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function orWhereClose()
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'orWhereClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Applies sorting with "ORDER BY ..."
     *
     * @param   mixed  $column    column name or [$column, $alias] or object
     * @param   string $direction direction of sorting
     *
     * @return  $this
     */
    public function orderBy($column, $direction = null)
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'orderBy',
            'args' => [
                $column,
                $direction
            ],
        ];

        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer $number maximum results to return
     *
     * @return  $this
     */
    public function limit($number)
    {
        $this->_dbPending[] = [
            'name' => 'setMaxResults',
            'args' => [$number],
        ];

        return $this;
    }

    /**
     * Enables or disables selecting only unique columns using "SELECT DISTINCT"
     *
     * @param   boolean $value enable or disable distinct columns
     *
     * @return  $this
     */
    public function distinct($value)
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'distinct',
            'args' => [$value],
        ];

        return $this;
    }

    /**
     * Choose the columns to select from.
     *
     * @param   mixed $columns column name or [$column, $alias] or object
     * @param   ...
     *
     * @return  $this
     */
    public function select($columns = null)
    {
        $columns = func_get_args();

        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'select',
            'args' => $columns,
        ];

        return $this;
    }

    /**
     * Choose the tables to select "FROM ..."
     *
     * @param   mixed $tables table name or [$table, $alias] or object
     * @param   ...
     *
     * @return  $this
     */
    public function from($tables)
    {
        $tables = func_get_args();

        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'from',
            'args' => $tables,
        ];

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param      $fromAlias
     * @param      $join
     * @param      $alias
     * @param null $condition
     * @return $this
     */
    public function leftJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->_dbPending[] = [
            'name' => 'leftJoin',
            'args' => [
                $fromAlias,
                $join,
                $alias,
                $condition
            ],
        ];

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param      $fromAlias
     * @param      $join
     * @param      $alias
     * @param null $condition
     * @return $this
     */
    public function join($fromAlias, $join, $alias, $condition = null)
    {
        $this->_dbPending[] = [
            'name' => 'join',
            'args' => [
                $fromAlias,
                $join,
                $alias,
                $condition
            ],
        ];

        return $this;
    }

    /**
     * Creates a "GROUP BY ..." filter.
     *
     * @param   mixed $columns column name or [$column, $alias] or object
     * @param   ...
     *
     * @return  $this
     */
    public function groupBy($columns)
    {
        $columns = func_get_args();

        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'groupBy',
            'args' => $columns,
        ];

        return $this;
    }

    /**
     * Alias of andHaving()
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function having($column, $op, $value = null)
    {
        return $this->andHaving($column, $op, $value);
    }

    /**
     * Creates a new "AND HAVING" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function andHaving($column, $op, $value = null)
    {
        $this->_dbPending[] = [
            'name' => 'andHaving',
            'args' => [
                $column,
                $op,
                $value
            ],
        ];

        return $this;
    }

    /**
     * Creates a new "OR HAVING" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function orHaving($column, $op, $value = null)
    {
        $this->_dbPending[] = [
            'name' => 'orHaving',
            'args' => [
                $column,
                $op,
                $value
            ],
        ];

        return $this;
    }

    /**
     * Alias of andHavingOpen()
     *
     * @return  $this
     */
    public function havingOpen()
    {
        return $this->andHavingOpen();
    }

    /**
     * Opens a new "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function andHavingOpen()
    {
        $this->_dbPending[] = [
            'name' => 'andHavingOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Opens a new "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function orHavingOpen()
    {
        $this->_dbPending[] = [
            'name' => 'orHavingOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function havingClose()
    {
        return $this->andHavingClose();
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function andHavingClose()
    {
        $this->_dbPending[] = [
            'name' => 'andHavingClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function orHavingClose()
    {
        $this->_dbPending[] = [
            'name' => 'orHavingClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer $number starting result number
     *
     * @return  $this
     */
    public function offset($number)
    {
        $this->_dbPending[] = [
            'name' => 'setFirstResult',
            'args' => [$number],
        ];

        return $this;
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     *
     * @param   integer $lifetime number of seconds to cache
     *
     * @return  $this
     * @uses    Base::$cacheLife
     */
    public function cached($lifetime = null)
    {
        $this->_dbPending[] = [
            'name' => 'cached',
            'args' => [$lifetime],
        ];

        return $this;
    }

    /**
     * Set the value of a parameter in the query.
     *
     * @param   string $param parameter key to replace
     * @param   mixed  $value value to use
     * @param null     $type
     * @return $this
     */
    public function param($param, $value, $type = null)
    {
        return $this->setParameter($param, $value, $type);
    }

    /**
     * Adds "USING ..." conditions for the last created JOIN statement.
     *
     * @param   string $columns column name
     *
     * @return  $this
     */
    public function using($columns)
    {
        $this->_dbPending[] = [
            'name' => 'using',
            'args' => [$columns],
        ];

        return $this;
    }

    /**
     * 绑定参数
     *
     * @param      $key
     * @param      $value
     * @param null $type
     * @return $this
     */
    public function setParameter($key, $value, $type = null)
    {
        $this->_dbPending[] = [
            'name' => 'setParameter',
            'args' => [$key, $value, $type],
        ];

        return $this;
    }
}

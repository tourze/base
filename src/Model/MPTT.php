<?php

namespace tourze\Model;

use tourze\Base\Exception\BaseException;
use tourze\Base\Exception\ValidationException;
use tourze\Base\Security\Validation;
use tourze\Model\Exception\ModelException;

/**
 * MPTT排序
 *
 * @package tourze\Model
 */
class MPTT extends Model
{

    /**
     * @access  public
     * @var     string  left column name
     */
    public $leftColumn = 'lft';

    /**
     * @access  public
     * @var     string  right column name
     */
    public $rightColumn = 'rgt';

    /**
     * @access  public
     * @var     string  level column name
     */
    public $levelColumn = 'lvl';

    /**
     * @access  public
     * @var     string  scope column name
     */
    public $scopeColumn = 'scope';

    /**
     * @access  public
     * @var     string  parent column name
     */
    public $parentColumn = 'parent_id';

    /**
     * Load the default column names.
     *
     * @access  public
     * @param   mixed $id parameter for find or object to load
     */
    public function __construct($id = null)
    {
        if ( ! isset($this->_sorting))
        {
            $this->_sorting = [$this->leftColumn => 'ASC'];
        }

        parent::__construct($id);
    }

    /**
     * Checks if the current node has any children.
     *
     * @access  public
     * @return  bool
     */
    public function hasChildren()
    {
        return ($this->size() > 2);
    }

    /**
     * Is the current node a leaf node?
     *
     * @access  public
     * @return  bool
     */
    public function isLeaf()
    {
        return ( ! $this->hasChildren());
    }

    /**
     * 确保target对象合法
     *
     * @access  public
     * @param   MPTT|int ORM_MPTT object or primary key value of target node
     * @return  MPTT
     */
    protected function ensureTarget($target)
    {
        if ( ! $target instanceof $this)
        {
            $class = self::className();
            $target = new $class($target);
        }
        return $target;
    }

    /**
     * Is the current node a descendant of the supplied node.
     *
     * @access  public
     * @param   MPTT|int ORM_MPTT object or primary key value of target node
     * @return  bool
     */
    public function isDescendant($target)
    {
        $target = $this->ensureTarget($target);

        return (
            $this->{$this->leftColumn} > $target->{$target->leftColumn}
            && $this->{$this->rightColumn} < $target->{$target->rightColumn}
            && $this->{$this->scopeColumn} = $target->{$target->scopeColumn}
        );
    }

    /**
     * Checks if the current node is a direct child of the supplied node.
     *
     * @access  public
     * @param   MPTT|int ORM_MPTT object or primary key value of target node
     * @return  bool
     */
    public function isChild($target)
    {
        $target = $this->ensureTarget($target);

        return ((int) $this->{$this->parentColumn} === (int) $target->pk());
    }

    /**
     * Checks if the current node is a direct parent of a specific node.
     *
     * @access  public
     * @param   MPTT|int ORM_MPTT object or primary key value of child node
     * @return  bool
     */
    public function isParent($target)
    {
        $target = $this->ensureTarget($target);

        return ((int) $this->pk() === (int) $target->{$this->parentColumn});
    }

    /**
     * Checks if the current node is a sibling of a supplied node.
     * (Both have the same direct parent)
     *
     * @access  public
     * @param   MPTT|int ORM_MPTT object or primary key value of target node
     * @return  bool
     */
    public function isSibling($target)
    {
        $target = $this->ensureTarget($target);

        if ((int) $this->pk() === (int) $target->pk())
        {
            return false;
        }
        return ((int) $this->{$this->parentColumn} === (int) $target->{$target->parentColumn});
    }

    /**
     * Checks if the current node is a root node.
     *
     * @access  public
     * @return  bool
     */
    public function isRoot()
    {
        return ($this->left() === 1);
    }

    /**
     * Checks if the current node is one of the parents of a specific node.
     *
     * @access  public
     * @param   MPTT|int id or object of parent node
     * @return  bool
     */
    public function is_in_parents($target)
    {
        $target = $this->ensureTarget($target);

        return $target->isDescendant($this);
    }

    /**
     * Overloaded save method.
     *
     * @access  public
     * @param \tourze\Base\Security\Validation $validation
     * @return mixed
     */
    public function save(Validation $validation = null)
    {
        if ( ! $this->loaded())
        {
            return $this->make_root($validation);
        }
        elseif ($this->loaded() === true)
        {
            return parent::save($validation);
        }

        return false;
    }

    /**
     * Creates a new node as root, or moves a node to root
     *
     * @access  public
     * @param Validation $validation
     * @param  int       $scope the new scope
     * @return MPTT
     */
    public function make_root(Validation $validation = null, $scope = null)
    {
        // If node already exists, and already root, exit
        if ($this->loaded() && $this->isRoot())
        {
            return $this;
        }

        // delete node space first
        if ($this->loaded())
        {
            $this->deleteSpace($this->left(), $this->size());
        }

        if (is_null($scope))
        {
            // Increment next scope
            $scope = self::get_next_scope();
        }
        elseif ( ! $this->scopeAvailable($scope))
        {
            return false;
        }

        $this->{$this->scopeColumn} = $scope;
        $this->{$this->levelColumn} = 1;
        $this->{$this->leftColumn} = 1;
        $this->{$this->rightColumn} = 2;
        $this->{$this->parentColumn} = null;

        return parent::save($validation);
    }

    /**
     * Sets the parentColumn value to the given targets column value. Returns the target ORM_MPTT object.
     *
     * @access  protected
     * @param   MPTT|int primary   key value or ORM_MPTT object of target node
     * @param            string    string        name of the targets nodes column to use
     * @return  MPTT
     */
    protected function parent_from($target, $column = null)
    {
        $target = $this->ensureTarget($target);

        if ($column === null)
        {
            $column = $target->primaryKey();
        }

        if ($target->loaded())
        {
            $this->{$this->parentColumn} = $target->{$column};
        }
        else
        {
            $this->{$this->parentColumn} = null;
        }

        return $target;
    }

    /**
     * Inserts a new node as the first child of the target node.
     *
     * @access  public
     * @param   MPTT|int primary key value or ORM_MPTT object of target node
     * @return  MPTT
     */
    public function insert_as_first_child($target)
    {
        $target = $this->parent_from($target);
        return $this->insert($target, $this->leftColumn, 1, 1);
    }

    /**
     * Inserts a new node as the last child of the target node.
     *
     * @access  public
     * @param   MPTT|int primary key value or ORM_MPTT object of target node
     * @return  MPTT
     */
    public function insertAsLastChild($target)
    {
        $target = $this->parent_from($target, $this->primaryKey());
        return $this->insert($target, $this->rightColumn, 0, 1);
    }

    /**
     * Inserts a new node as a previous sibling of the target node.
     *
     * @access  public
     * @param   MPTT|int primary key value or ORM_MPTT object of target node
     * @return  MPTT
     */
    public function insertAsPrevSibling($target)
    {
        $target = $this->parent_from($target, $this->parentColumn);
        return $this->insert($target, $this->leftColumn, 0, 0);
    }

    /**
     * Inserts a new node as the next sibling of the target node.
     *
     * @access  public
     * @param   MPTT|int primary key value or ORM_MPTT object of target node
     * @return  MPTT
     */
    public function insertAsNextSibling($target)
    {
        $target = $this->parent_from($target, $this->parentColumn);
        return $this->insert($target, $this->rightColumn, 1, 0);
    }

    /**
     * Insert the object
     *
     * @param   integer|MPTT    $target       key value or ORM_MPTT object of target node.
     * @param            string $copyLeftFrom target object property to take new left value from
     * @param            int    $leftOffset   offset for left value
     * @param            int    $levelOffset  offset for level value
     * @return \tourze\Model\MPTT
     * @throws \Exception
     * @throws \tourze\Base\Exception\ValidationException
     */
    protected function insert($target, $copyLeftFrom, $leftOffset, $levelOffset)
    {
        // Insert should only work on new nodes.. if its already it the tree it needs to be moved!
        if ($this->loaded())
        {
            return false;
        }


        if ( ! $target instanceof $this)
        {
            $target = self::factory($this->object_name(), [$this->primary_key() => $target]);

            if ( ! $target->loaded())
            {
                return false;
            }
        }
        else
        {
            $target->reload();
        }

        $this->{$this->leftColumn} = $target->{$copyLeftFrom} + $leftOffset;
        $this->{$this->rightColumn} = $this->{$this->leftColumn} + 1;
        $this->{$this->levelColumn} = $target->{$this->levelColumn} + $levelOffset;
        $this->{$this->scopeColumn} = $target->{$this->scopeColumn};

        $this->createSpace($this->{$this->leftColumn});

        try
        {
            parent::save();
        }
        catch (ValidationException $e)
        {
            // We had a problem saving, make sure we clean up the tree
            $this->deleteSpace($this->left());
            throw $e;
        }

        return $this;
    }

    /**
     * Deletes the current node and all descendants.
     *
     * @access  public
     * @param null $query
     * @return \tourze\Model\Model|void
     * @throws \Exception
     * @throws \tourze\Base\Exception\BaseException
     */
    public function delete($query = null)
    {
        if ($query !== null)
        {
            throw new BaseException('MPTT does not support passing a query object to delete()');
        }

        try
        {
            DB::delete($this->_table_name)
                ->where($this->leftColumn, ' >=', $this->left())
                ->where($this->rightColumn, ' <= ', $this->right())
                ->where($this->scopeColumn, ' = ', $this->scope())
                ->execute($this->_db);

            $this->deleteSpace($this->left(), $this->size());
        }
        catch (BaseException $e)
        {
            throw $e;
        }
    }

    public function moveToFirstChild($target)
    {
        $target = $this->parent_from($target, $this->primaryKey());
        return $this->move($target, true, 1, 1, true);
    }

    public function moveToLastChild($target)
    {
        $target = $this->parent_from($target, $this->primaryKey());
        return $this->move($target, false, 0, 1, true);
    }

    public function moveToPrevSibling($target)
    {
        $target = $this->parent_from($target, $this->parentColumn);
        return $this->move($target, true, 0, 0, false);
    }

    public function moveToNextSibling($target)
    {
        $target = $this->parent_from($target, $this->parentColumn);
        return $this->move($target, false, 1, 0, false);
    }

    protected function move($target, $leftColumn, $left_offset, $level_offset, $allow_root_target)
    {
        if ( ! $this->loaded())
        {
            return false;
        }

        // store the changed parent id before reload
        $parent_id = $this->{$this->parentColumn};

        $this->reload();

        try
        {
            if ( ! $target instanceof $this)
            {
                $target = self::factory($this->object_name(), [$this->primaryKey() => $target]);

                if ( ! $target->loaded())
                {
                    return false;
                }
            }
            else
            {
                $target->reload();
            }

            // Stop $this being moved into a descendant or itself or disallow if target is root
            if ($target->isDescendant($this)
                OR $this->{$this->primaryKey()} === $target->{$this->primaryKey()}
                OR ($allow_root_target === false AND $target->isRoot())
            )
            {
                return false;
            }

            if ($level_offset > 0)
            {
                // We're moving to a child node so add 1 to left offset.
                $left_offset = ($leftColumn === true) ? ($target->left() + 1) : ($target->right() + $left_offset);
            }
            else
            {
                $left_offset = ($leftColumn === true) ? $target->left() : ($target->right() + $left_offset);
            }

            $level_offset = $target->level() - $this->level() + $level_offset;
            $size = $this->size();

            $this->createSpace($left_offset, $size);

            $this->reload();

            $offset = ($left_offset - $this->left());

            $this->_db->query(Database::UPDATE, 'UPDATE ' . $this->_db->quote_table($this->_table_name) . ' SET `'
                . $this->leftColumn . '` = `' . $this->leftColumn . '` + '
                . $offset . ', `' . $this->rightColumn . '` =  `' . $this->rightColumn . '` + '
                . $offset . ', `' . $this->levelColumn . '` =  `' . $this->levelColumn . '` + '
                . $level_offset . ', `' . $this->scopeColumn . '` = ' . $target->scope()
                . ' WHERE `' . $this->leftColumn . '` >= ' . $this->left() . ' AND `'
                . $this->rightColumn . '` <= ' . $this->right() . ' AND `'
                . $this->scopeColumn . '` = ' . $this->scope(), true);

            $this->deleteSpace($this->left(), $size);
        }
        catch (BaseException $e)
        {
            throw $e;
        }

        // all went well so save the parent_id if changed
        if ($parent_id != $this->{$this->parentColumn})
        {
            $this->{$this->parentColumn} = $parent_id;
            $this->save();
        }

        $this->reload();

        return $this;
    }

    /**
     * Returns the next available value for scope.
     *
     * @access  protected
     * @return  int
     **/
    protected function get_next_scope()
    {
        $scope = DB::select(DB::expr('IFNULL(MAX(`' . $this->scopeColumn . '`), 0) as scope'))
            ->from($this->_table_name)
            ->execute($this->_db)
            ->current();

        if ($scope AND intval($scope['scope']) > 0)
        {
            return intval($scope['scope']) + 1;
        }

        return 1;
    }

    /**
     * Returns the root node of the current object instance.
     *
     * @access  public
     * @param   int $scope
     * @return FALSE|\tourze\Model\MPTT
     * @throws \tourze\Model\Exception\ModelException
     */
    public function root($scope = null)
    {
        if (is_null($scope) AND $this->loaded())
        {
            $scope = $this->scope();
        }
        elseif (is_null($scope) AND ! $this->loaded())
        {
            throw new ModelException(':method must be called on an ORM_MPTT object instance.', [
                ':method' => 'root',
            ]);
        }

        return new self([$this->leftColumn => 1, $this->scopeColumn => $scope]);
    }

    /**
     * Returns all root node's
     *
     * @access  public
     * @return  MPTT
     */
    public function roots()
    {
        return (new self)
            ->where($this->leftColumn, '=', 1)
            ->findAll();
    }

    /**
     * Returns the parent node of the current node
     *
     * @access  public
     * @return  MPTT
     */
    public function parent()
    {
        if ($this->isRoot())
        {
            return null;
        }
        return $this->ensureTarget($this->{$this->parentColumn});
    }

    /**
     * Returns all of the current nodes parents.
     *
     * @access  public
     * @param   bool   $root               include root node
     * @param   bool   $with_self          include current node
     * @param   string $direction          direction to order the left column by
     * @param   bool   $direct_parent_only retrieve the direct parent only
     * @return  MPTT
     */
    public function parents($root = true, $with_self = false, $direction = 'ASC', $direct_parent_only = false)
    {
        $suffix = $with_self ? '=' : '';

        $query = (new self)
            ->where($this->leftColumn, '<' . $suffix, $this->left())
            ->where($this->rightColumn, '>' . $suffix, $this->right())
            ->where($this->scopeColumn, '=', $this->scope())
            ->orderBy($this->leftColumn, $direction);

        if ( ! $root)
        {
            $query->where($this->leftColumn, '!=', 1);
        }

        if ($direct_parent_only)
        {
            $query
                ->where($this->levelColumn, '=', $this->level() - 1)
                ->limit(1);
        }

        return $query->findAll();
    }

    /**
     * Returns direct children of the current node.
     *
     * @access  public
     * @param   bool   $self      include the current node
     * @param   string $direction direction to order the left column by
     * @param bool|int $limit     number of children to get
     * @return MPTT
     */
    public function children($self = false, $direction = 'ASC', $limit = false)
    {
        return $this->descendants($self, $direction, true, false, $limit);
    }

    /**
     * Returns a full hierarchical tree, with or without scope checking.
     *
     * @access  public
     * @param   mixed $scope only retrieve nodes with specified scope
     * @return  object
     */
    public function fullTree($scope = null)
    {
        $result = new self;

        if ( ! is_null($scope))
        {
            $result->where($this->scopeColumn, '=', $scope);
        }
        else
        {
            $result
                ->orderBy($this->scopeColumn, 'ASC')
                ->orderBy($this->leftColumn, 'ASC');
        }

        return $result->findAll();
    }

    /**
     * Returns the siblings of the current node
     *
     * @access  public
     * @param   bool   $self      include the current node
     * @param   string $direction direction to order the left column by
     * @return  MPTT
     */
    public function siblings($self = false, $direction = 'ASC')
    {
        $query = (new self)
            ->where($this->leftColumn, '>', $this->parent()->left())
            ->where($this->rightColumn, '<', $this->parent()->right())
            ->where($this->scopeColumn, '=', $this->scope())
            ->where($this->levelColumn, '=', $this->level())
            ->orderBy($this->leftColumn, $direction);

        if ( ! $self)
        {
            $query->where($this->primaryKey(), '<>', $this->pk());
        }

        return $query->findAll();
    }

    /**
     * Returns the leaves of the current node.
     *
     * @access  public
     * @param   bool   $self      include the current node
     * @param   string $direction direction to order the left column by
     * @return  MPTT
     */
    public function leaves($self = false, $direction = 'ASC')
    {
        return $this->descendants($self, $direction, true, true);
    }

    /**
     * Returns the descendants of the current node.
     *
     * @access  public
     * @param   bool   $self                 include the current node
     * @param   string $direction            direction to order the left column by.
     * @param   bool   $direct_children_only include direct children only
     * @param   bool   $leaves_only          include leaves only
     * @param bool|int $limit                number of results to get
     * @return MPTT
     */
    public function descendants($self = false, $direction = 'ASC', $direct_children_only = false, $leaves_only = false, $limit = false)
    {
        $left_operator = $self ? '>=' : '>';
        $right_operator = $self ? '<=' : '<';

        $query = (new self)
            ->where($this->leftColumn, $left_operator, $this->left())
            ->where($this->rightColumn, $right_operator, $this->right())
            ->where($this->scopeColumn, '=', $this->scope())
            ->orderBy($this->leftColumn, $direction);

        if ($direct_children_only)
        {
            if ($self)
            {
                $query
                    ->andWhereOpen()
                    ->where($this->levelColumn, '=', $this->level())
                    ->orWhere($this->levelColumn, '=', $this->level() + 1)
                    ->andWhereClose();
            }
            else
            {
                $query->where($this->levelColumn, '=', $this->level() + 1);
            }
        }

        if ($leaves_only)
        {
            $query->where($this->rightColumn, '=', $this->leftColumn . ' + 1');
        }

        if ($limit !== false)
        {
            $query->limit($limit);
        }

        return $query->findAll();
    }

    /**
     * Adds space to the tree for adding or inserting nodes.
     *
     * @access  protected
     * @param   int $start start position
     * @param   int $size  size of the gap to add [optional]
     * @return  void
     */
    protected function createSpace($start, $size = 2)
    {
        $this
            ->db()
            ->createQueryBuilder()
            ->update($this->tableName(), $this->objectName())
            ->set($this->leftColumn, $this->leftColumn . ' + ' . $size)
            ->where($this->leftColumn, '>=', $start)
            ->where($this->scopeColumn, '=', $this->scope())
            ->execute();

        $this
            ->db()
            ->createQueryBuilder()
            ->update($this->tableName(), $this->objectName())
            ->set($this->rightColumn, $this->rightColumn . ' + ' . $size)
            ->where($this->rightColumn, '>=', $start)
            ->where($this->scopeColumn, '=', $this->scope())
            ->execute();
    }

    /**
     * Removes space from the tree after deleting or moving nodes.
     *
     * @access  protected
     * @param   int $start start position
     * @param   int $size  size of the gap to remove [optional]
     * @return  void
     */
    protected function deleteSpace($start, $size = 2)
    {
        $this
            ->db()
            ->createQueryBuilder()
            ->update($this->tableName(), $this->objectName())
            ->set($this->leftColumn, $this->leftColumn . ' - ' . $size)
            ->where($this->leftColumn, '>=', $start)
            ->where($this->scopeColumn, '=', $this->scope())
            ->execute();

        $this->db()
            ->createQueryBuilder()
            ->update($this->tableName(), $this->objectName())
            ->set($this->rightColumn, $this->rightColumn . ' - ' . $size)
            ->where($this->rightColumn, '>=', $start)
            ->where($this->scopeColumn, '=', $this->scope())
            ->execute();
    }

    /**
     * Returns the value of the current nodes left column.
     *
     * @access  public
     * @return  int
     */
    public function left()
    {
        return (int) $this->{$this->leftColumn};
    }

    /**
     * Returns the value of the current nodes right column.
     *
     * @access  public
     * @return  int
     */
    public function right()
    {
        return (int) $this->{$this->rightColumn};
    }

    /**
     * Returns the value of the current nodes level column.
     *
     * @access  public
     * @return  int
     */
    public function level()
    {
        return (int) $this->{$this->levelColumn};
    }

    /**
     * Returns the value of the current nodes scope column.
     *
     * @access  public
     * @return  int
     */
    public function scope()
    {
        return (int) $this->{$this->scopeColumn};
    }

    /**
     * Returns the size of the current node.
     *
     * @access  public
     * @return  int
     */
    public function size()
    {
        return $this->right() - $this->left() + 1;
    }

    /**
     * Returns the number of descendants the current node has.
     *
     * @access  public
     * @return  int
     */
    public function count()
    {
        return ($this->size() - 2) / 2;
    }

    /**
     * Checks if the supplied scope is available.
     *
     * @access  protected
     * @param   int $scope scope to check availability of
     * @return  bool
     */
    protected function scopeAvailable($scope)
    {
        return (new self)->where($this->scopeColumn, '=', $scope)->countAll() > 0;
    }

    /**
     * Rebuilds the tree using the parentColumn. Order of the tree is not guaranteed
     * to be consistent with structure prior to reconstruction. This method will reduce the
     * tree structure to eliminating any holes. If you have a child node that is outside of
     * the left/right constraints it will not be moved under the root.
     *
     * @access  public
     * @param   int  $left   Starting value for left branch
     * @param   MPTT $target Target node to use as root
     * @return  int
     */
    public function rebuildTree($left = 1, $target = null)
    {
        // check if using target or self as root and load if not loaded
        if (is_null($target) AND ! $this->loaded())
        {
            return false;
        }
        elseif (is_null($target))
        {
            $target = $this;
        }

        if ( ! $target->loaded())
        {
            $target->_load();
        }

        // Use the current node left value for entire tree
        if (is_null($left))
        {
            $left = $target->{$target->leftColumn};
        }

        $right = $left + 1;
        $children = $target->children();

        /** @var MPTT $child */
        foreach ($children as $child)
        {
            $right = $child->rebuildTree($right);
        }

        $target->{$target->leftColumn} = $left;
        $target->{$target->rightColumn} = $right;
        $target->save();

        return $right + 1;
    }

    /**
     * Magic get function, maps field names to class functions.
     *
     * @access  public
     * @param   string $column name of the field to get
     * @return  mixed
     */
    public function get($column)
    {
        switch ($column)
        {
            case 'parent':
                return $this->parent();
            case 'parents':
                return $this->parents();
            case 'children':
                return $this->children();
            case 'first_child':
                return $this->children(false, 'ASC', 1);
            case 'last_child':
                return $this->children(false, 'DESC', 1);
            case 'siblings':
                return $this->siblings();
            case 'root':
                return $this->root();
            case 'roots':
                return $this->roots();
            case 'leaves':
                return $this->leaves();
            case 'descendants':
                return $this->descendants();
            case 'fullTree':
                return $this->fulltree();
            default:
                return parent::get($column);
        }
    }
}

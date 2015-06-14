<?php

namespace tourze\Model\Exception;

use Exception;
use tourze\Base\Security\Validation;

/**
 * ORM Validation exceptions.
 *
 * @package    Base/ORM
 * @author     YwiSax
 */
class ValidationException extends ModelException
{

    /**
     * Array of validation objects
     *
     * @var array
     */
    protected $_objects = [];

    /**
     * The alias of the main ORM model this exception was created for
     *
     * @var string
     */
    protected $_alias = null;

    /**
     * Constructs a new exception for the specified model
     *
     * @param  string     $alias   The alias to use when looking for error messages
     * @param  Validation $object  The Validation object of the model
     * @param  string     $message The error message
     * @param  array      $values  The array of values for the error message
     * @param  integer    $code    The error code for the exception
     * @param Exception   $previous
     */
    public function __construct($alias, Validation $object, $message = 'Failed to validate array', array $values = null, $code = 0, Exception $previous = null)
    {
        $this->_alias = $alias;
        $this->_objects['_object'] = $object;
        $this->_objects['_hasMany'] = false;

        parent::__construct($message, $values, $code, $previous);
    }

    /**
     * Adds a Validation object to this exception
     *     // The following will add a validation object for a profile model
     *     // inside the exception for a user model.
     *     $e->addObject('profile', $validation);
     *     // The errors array will now look something like this
     *     // array
     *     // (
     *     //   'username' => 'This field is required',
     *     //   'profile'  => array
     *     //   (
     *     //     'first_name' => 'This field is required',
     *     //   ),
     *     // );
     *
     * @param  string     $alias   The relationship alias from the model
     * @param  Validation $object  The Validation object to merge
     * @param  mixed      $hasMany The array key to use if this exception can be merged multiple times
     * @return ValidationException
     */
    public function addObject($alias, Validation $object, $hasMany = false)
    {
        // We will need this when generating errors
        $this->_objects[ $alias ]['_hasMany'] = (false !== $hasMany);

        if (true === $hasMany)
        {
            // This is most likely a hasMany relationship
            $this->_objects[ $alias ][]['_object'] = $object;
        }
        elseif ($hasMany)
        {
            // This is most likely a hasMany relationship
            $this->_objects[ $alias ][ $hasMany ]['_object'] = $object;
        }
        else
        {
            $this->_objects[ $alias ]['_object'] = $object;
        }

        return $this;
    }

    /**
     * Merges an ValidationException object into the current exception
     * Useful when you want to combine errors into one array
     *
     * @param  ValidationException $object  The exception to merge
     * @param  mixed               $hasMany The array key to use if this exception can be merged multiple times
     * @return ValidationException
     */
    public function merge(ValidationException $object, $hasMany = false)
    {
        $alias = $object->alias();

        // We will need this when generating errors
        $this->_objects[ $alias ]['_hasMany'] = (false !== $hasMany);

        if (true === $hasMany)
        {
            // This is most likely a hasMany relationship
            $this->_objects[ $alias ][] = $object->objects();
        }
        elseif ($hasMany)
        {
            // This is most likely a hasMany relationship
            $this->_objects[ $alias ][ $hasMany ] = $object->objects();
        }
        else
        {
            $this->_objects[ $alias ] = $object->objects();
        }

        return $this;
    }

    /**
     * Returns a merged array of the errors from all the Validation objects in this exception
     *     // Will load Model_User errors from messages/orm-validation/user.php
     *     $e->errors('orm-validation');
     *
     * @param   string $directory Directory to load error messages from
     * @param   mixed  $translate Translate the message
     * @return  array
     * @see generateErrors()
     */
    public function errors($directory = null, $translate = true)
    {
        return $this->generateErrors($this->_alias, $this->_objects, $directory, $translate);
    }

    /**
     * Recursive method to fetch all the errors in this exception
     *
     * @param  string $alias     Alias to use for messages file
     * @param  array  $array     Array of Validation objects to get errors from
     * @param  string $directory Directory to load error messages from
     * @param  mixed  $translate Translate the message
     * @return array
     */
    protected function generateErrors($alias, array $array, $directory, $translate)
    {
        $errors = [];

        foreach ($array as $key => $object)
        {
            if (is_array($object))
            {
                $errors[ $key ] = ($key === '_external')
                    // Search for errors in $alias/_external.php
                    ? $this->generateErrors($alias . '/' . $key, $object, $directory, $translate)
                    // Regular models get their own file not nested within $alias
                    : $this->generateErrors($key, $object, $directory, $translate);
            }
            elseif ($object instanceof Validation)
            {
                if (null === $directory)
                {
                    // Return the raw errors
                    $file = null;
                }
                else
                {
                    $file = trim($directory . '/' . $alias, '/');
                }

                $errors += $object->errors($file, $translate);
            }
        }

        return $errors;
    }

    /**
     * Returns the protected _objects property from this exception
     *
     * @return array
     */
    public function objects()
    {
        return $this->_objects;
    }

    /**
     * Returns the protected _alias property from this exception
     *
     * @return string
     */
    public function alias()
    {
        return $this->_alias;
    }
}

<?php

namespace tourze\View;

use Exception;
use tourze\Base\Exception\BaseException;
use tourze\View\Exception\ViewException;

/**
 * Acts as an object wrapper for HTML pages with embedded PHP, called "views".
 * Variables can be assigned with the view object and referenced locally within
 * the view.
 *
 * @package    Base
 * @category   Base
 * @author     YwiSax
 */
class View
{

    public static $ext = '.php';

    /**
     * @var array 存放视图文件的目录列表
     */
    protected static $_viewPaths = [];

    /**
     * @var array 全局变量
     */
    protected static $_globalData = [];

    /**
     * @param string $path 视图加载目录
     */
    public static function addPath($path)
    {
        self::$_viewPaths[] = $path;
    }

    /**
     * Returns a new View object. If you do not define the "file" parameter,
     * you must call [View::setFilename].
     *
     *     $view = View::factory($file);
     *
     * @param   string $file view filename
     * @param   array  $data array of values
     *
     * @return  View
     */
    public static function factory($file = null, array $data = null)
    {
        return new View($file, $data);
    }

    /**
     * Captures the output that is generated when a view is included.
     * The view data will be extracted to make local variables. This method
     * is static to prevent object scope resolution.
     *     $output = View::capture($file, $data);
     *
     * @param   string $viewFilename filename
     * @param   array  $viewData     variables
     *
     * @return  string
     * @throws  Exception
     */
    protected static function capture($viewFilename, array $viewData)
    {
        // Import the view variables to local namespace
        extract($viewData, EXTR_SKIP);

        if (View::$_globalData)
        {
            // Import the global view variables to local namespace
            extract(View::$_globalData, EXTR_SKIP | EXTR_REFS);
        }

        // Capture the view output
        ob_start();

        try
        {
            // Load the view within the current scope
            include $viewFilename;
        }
        catch (Exception $e)
        {
            // Delete the output buffer
            ob_end_clean();

            // Re-throw the exception
            throw $e;
        }

        // Get the captured output and close the buffer
        return ob_get_clean();
    }

    /**
     * Sets a global variable, similar to [View::set], except that the
     * variable will be accessible to all views.
     *     View::setGlobal($name, $value);
     *
     * @param   string $key   variable name or an array of variables
     * @param   mixed  $value value
     *
     * @return  void
     */
    public static function setGlobal($key, $value = null)
    {
        if (is_array($key))
        {
            foreach ($key as $key2 => $value)
            {
                View::$_globalData[$key2] = $value;
            }
        }
        else
        {
            View::$_globalData[$key] = $value;
        }
    }

    /**
     * Assigns a global variable by reference, similar to [View::bind], except
     * that the variable will be accessible to all views.
     *     View::bindGlobal($key, $value);
     *
     * @param   string $key   variable name
     * @param   mixed  $value referenced variable
     *
     * @return  void
     */
    public static function bindGlobal($key, & $value)
    {
        View::$_globalData[$key] =& $value;
    }

    // View filename
    protected $_file;

    // Array of local variables
    protected $_data = [];

    /**
     * Sets the initial view filename and local data. Views should almost
     * always only be created using [View::factory].
     *     $view = new View($file);
     *
     * @param   string $file view filename
     * @param   array  $data array of values
     *
     * @throws  ViewException
     * @uses    View::setFilename
     */
    public function __construct($file = null, array $data = null)
    {
        if (null !== $file)
        {
            $this->setFilename($file);
        }

        if (null !== $data)
        {
            // Add the values to the current data
            $this->_data = $data + $this->_data;
        }
    }

    /**
     * Magic method, searches for the given variable and returns its value.
     * Local variables will be returned before global variables.
     *     $value = $view->foo;
     * [!!] If the variable has not yet been set, an exception will be thrown.
     *
     * @param   string $key variable name
     *
     * @return  mixed
     * @throws  BaseException
     */
    public function & __get($key)
    {
        if (array_key_exists($key, $this->_data))
        {
            return $this->_data[$key];
        }
        elseif (array_key_exists($key, View::$_globalData))
        {
            return View::$_globalData[$key];
        }
        else
        {
            throw new BaseException('View variable is not set: :var',
                [':var' => $key]);
        }
    }

    /**
     * Magic method, calls [View::set] with the same parameters.
     *     $view->foo = 'something';
     *
     * @param   string $name  variable name
     * @param   mixed  $value value
     *
     * @return  void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Magic method, determines if a variable is set.
     *     isset($view->foo);
     * [!!] `null` variables are not considered to be set by [isset](http://php.net/isset).
     *
     * @param   string $name variable name
     *
     * @return  boolean
     */
    public function __isset($name)
    {
        return (isset($this->_data[$name]) || isset(View::$_globalData[$name]));
    }

    /**
     * 魔术方法，注销指定变量
     *
     *     unset($view->foo);
     *
     * @param   string $name variable name
     *
     * @return  void
     */
    public function __unset($name)
    {
        unset($this->_data[$name], View::$_globalData[$name]);
    }

    /**
     * Magic method, returns the output of [View::render].
     *
     * @return  string
     * @uses    View::render
     */
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch (Exception $e)
        {
            /**
             * Display the exception message.
             * We use this method here because it's impossible to throw an
             * exception from __toString().
             */
            $errorResponse = BaseException::_handler($e);
            return $errorResponse->body;
        }
    }

    /**
     * Sets the view filename.
     *     $view->setFilename($file);
     *
     * @param   string $file view filename
     *
     * @return  View
     * @throws  ViewException
     */
    public function setFilename($file)
    {
        $found = false;
        foreach (self::$_viewPaths as $path)
        {
            if ( ! $found && is_file($path . $file . self::$ext))
            {
                $found = $path . $file . self::$ext;
            }
        }

        if ( ! $found)
        {
            throw new ViewException('The requested view :file could not be found', [
                ':file' => $file,
            ]);
        }

        $this->_file = $found;

        return $this;
    }

    /**
     * Assigns a variable by name. Assigned values will be available as a
     * variable within the view file:
     *     // This value can be accessed as $foo within the view
     *     $view->set('foo', 'my value');
     * You can also use an array to set several values at once:
     *     // Create the values $food and $beverage in the view
     *     $view->set(['food' => 'bread', 'beverage' => 'water']);
     *
     * @param   string $key   variable name or an array of variables
     * @param   mixed  $value value
     *
     * @return  $this
     */
    public function set($key, $value = null)
    {
        if (is_array($key))
        {
            foreach ($key as $name => $value)
            {
                $this->_data[$name] = $value;
            }
        }
        else
        {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Assigns a value by reference. The benefit of binding is that values can
     * be altered without re-setting them. It is also possible to bind variables
     * before they have values. Assigned values will be available as a
     * variable within the view file:
     *     // This reference can be accessed as $ref within the view
     *     $view->bind('ref', $bar);
     *
     * @param   string $key   variable name
     * @param   mixed  $value referenced variable
     *
     * @return  $this
     */
    public function bind($key, & $value)
    {
        $this->_data[$key] =& $value;

        return $this;
    }

    /**
     * Renders the view object to a string. Global and local data are merged
     * and extracted to create local variables within the view file.
     *     $output = $view->render();
     * [!!] Global variables with the same key name as local variables will be
     * overwritten by the local variable.
     *
     * @param   string $file view filename
     *
     * @return  string
     * @throws  ViewException
     * @uses    View::capture
     */
    public function render($file = null)
    {
        if (null !== $file)
        {
            $this->setFilename($file);
        }

        if (empty($this->_file))
        {
            throw new ViewException('You must set the file to use within your view before rendering');
        }

        // Combine local and global data and capture the output
        return View::capture($this->_file, $this->_data);
    }

}

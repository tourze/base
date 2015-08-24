<?php

namespace tourze\View;

use Exception;
use tourze\Base\Debug;
use tourze\Base\Helper\Arr;
use tourze\View\Exception\ViewException;

/**
 * 基础的基于PHP实现的视图
 *
 * @package tourze\View
 */
abstract class Base
{

    /**
     * @var string 默认视图文件后缀
     */
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
        if ( ! isset(self::$_viewPaths[$path]))
        {
            self::$_viewPaths[$path] = $path;
        }
    }

    /**
     * 返回指定视图文件和数据的视图对象
     *
     * @param string $file 视图文件
     * @param array  $data 视图数据
     * @return Base
     */
    public static function factory($file = null, array $data = null)
    {
        $class = get_called_class();
        return new $class($file, $data);
    }

    /**
     * 设置全局变量
     *
     * @param  string $key   全局变量名
     * @param  mixed  $value 全局变量值
     * @return void
     */
    public static function setGlobal($key, $value = null)
    {
        if (is_array($key))
        {
            foreach ($key as $key2 => $value)
            {
                self::$_globalData[$key2] = $value;
            }
        }
        else
        {
            self::$_globalData[$key] = $value;
        }
    }

    /**
     * 绑定参数到全局变量数据
     *
     * @param  string $key   全局变量名
     * @param  mixed  $value 全局变量值
     * @return void
     */
    public static function bindGlobal($key, & $value)
    {
        self::$_globalData[$key] =& $value;
    }

    /**
     * 读取全局变量
     *
     * @param string $key     键名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public static function getGlobal($key, $default = null)
    {
        return Arr::get(self::$_globalData, $key, $default);
    }

    /**
     * @var string 当前视图文件
     */
    protected $_file;

    /**
     * @var array 视图变量数据
     */
    protected $_data = [];

    /**
     * 构造函数
     *
     * @param  string $file 视图文件名
     * @param  array  $data 数据变量
     * @throws ViewException
     * @uses   View::setFilename
     */
    public function __construct($file = null, array $data = null)
    {
        if (null !== $file)
        {
            $this->setFilename($file);
        }

        if (null !== $data)
        {
            $this->_data = Arr::merge($this->_data, $data);
        }
    }

    /**
     * 获取视图的最终输入
     *
     * @param  string $viewFilename 文件名
     * @param  array  $viewData     变量
     * @return string
     * @throws Exception
     */
    abstract protected function capture($viewFilename, array $viewData);

    /**
     * 魔术方法，用户读取当前视图数据（包含全局数据）
     *
     * @param   string $key 变量名
     * @return  mixed
     * @throws  ViewException
     */
    public function & __get($key)
    {
        if (array_key_exists($key, $this->_data))
        {
            return $this->_data[$key];
        }
        elseif (array_key_exists($key, View::$_globalData))
        {
            return self::$_globalData[$key];
        }
        else
        {
            throw new ViewException('View variable is not set: :var', [
                ':var' => $key
            ]);
        }
    }

    /**
     * 魔术方法，用户保存变量数据
     *
     * @param  string $name  变量名
     * @param  mixed  $value 变量值
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * 魔术方法，检测指定的变量值是否存在
     *
     * @param  string $name 变量名
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_data) || array_key_exists($name, self::$_globalData);
    }

    /**
     * 魔术方法，注销指定变量
     *
     * @param  string $name 变量名
     * @return void
     */
    public function __unset($name)
    {
        unset($this->_data[$name], self::$_globalData[$name]);
    }

    /**
     * 返回当前视图的最终渲染
     *
     * @return  string
     */
    public function __toString()
    {
        try
        {
            return $this->render();
        }
        catch (Exception $e)
        {
            return $errorResponse = Debug::debugger()->handleException($e);
        }
    }

    /**
     * 设置当前的视图文件名
     *
     * @param  string $file 视图文件
     * @return View
     * @throws ViewException
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
     * 设置视图变量
     *
     * @param  string $key   变量名，或者包含了所有变量的关联数组
     * @param  mixed  $value 值
     * @return $this
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
     * 绑定变量引用值
     *
     *     $view->bind('ref', $bar);
     *
     * @param  string $key   变量名
     * @param  mixed  $value 引用值
     * @return $this
     */
    public function bind($key, & $value)
    {
        $this->_data[$key] =& $value;

        return $this;
    }

    /**
     * 渲染视图
     *
     * @param  string $file 视图文件名
     * @return string
     * @throws ViewException
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

        return $this->capture($this->_file, $this->_data);
    }

}

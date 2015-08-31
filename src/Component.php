<?php

namespace tourze\Base;

/**
 * 组件基础类
 *
 * @package tourze\Base
 */
class Component extends Object
{

    /**
     * @var bool 当前组件是否可被初始化，如果不可持久化，在每次系统初始化时，会自动注销
     */
    public $persistence = true;

}

<?php

namespace tourze\Html\Widget;

use tourze\Html\Widget;
use tourze\Html\Widget\Navigation\Menu;

class Navigation extends Widget
{

    static private $_menus = [];

    /**
     * Alias for "self::registerMenu"
     *
     * @param       $menuName
     * @param array $preset
     * @return Menu
     */
    static public function forge($menuName, array $preset = [])
    {
        return self::registerMenu($menuName, $preset);
    }

    /**
     * Register new menu item
     * If menu exists, return it, if not, create new one
     *
     * @param       $menuName
     * @param array $preset
     * @return  Menu
     */
    static public function registerMenu($menuName, array $preset = [])
    {

        $menuName = (string) $menuName;

        if (isset(self::$_menus[$menuName]) && self::$_menus[$menuName] instanceof Menu)
        {
            return self::$_menus[$menuName];
        }
        else
        {
            self::$_menus[$menuName] = new Menu($menuName, $preset);
            return self::$_menus[$menuName];
        }

    }

    /**
     * Register new item for menu
     *
     * @param                 $menuName
     * @param Navigation\Item $item
     */
    static public function registerItem($menuName, Navigation\Item $item)
    {
        $menuName = (string) $menuName;
        $menu = self::registerMenu($menuName);
        $menu->registerItem($item);
    }

    /**
     *  Un-register menu item
     *
     * @param $menuName
     * @param $item
     * @return bool, true if success, otherwise false
     */
    static public function removeItem($menuName, $item)
    {
        $menu = self::getMenu($menuName);
        if ($menu)
        {
            return $menu->removeItem($item);
        }

        return false;
    }

    /**
     * Get registered menu object. or null
     *
     * @param $menuName
     * @return null|Menu
     */
    static public function getMenu($menuName)
    {
        return (isset(self::$_menus[$menuName]) && self::$_menus[$menuName] instanceof Menu) ? self::$_menus[$menuName] : null;
    }

    /**
     * Set active element
     *
     * @param      $menuName
     * @param      $item
     * @param bool $single
     */
    static public function setActive($menuName, $item, $single = true)
    {
        $menu = self::getMenu($menuName);
        if ($menu)
        {
            $menu->setActive($item, $single);
        }
    }

    /**
     * Render navigation
     *
     * @param $menuName
     * @param $attributes
     * @return null
     */
    static public function render($menuName, array $attributes = [])
    {
        $menu = self::getMenu($menuName);
        if ($menu)
        {
            return $menu->render($attributes);
        }

        return null;
    }

}

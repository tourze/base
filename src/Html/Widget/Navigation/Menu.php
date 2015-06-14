<?php

namespace tourze\Html\Widget\Navigation;

use Exception;
use tourze\Html\Tag\A;

/**
 * @since      File available since Release 1.0
 */
class Menu
{

    /**
     * Navigation name
     *
     * @var
     */
    private $_name;

    /**
     * All menu elements
     *
     * @var array
     */
    private $_elements = [];

    /**
     * Keys of registered elements
     *
     * @var array
     */
    private $_registeredElements = [];

    /**
     * Set active items
     *
     * @var array
     */
    private $_activeItems = [];

    /**
     * Build tree
     *
     * @var null
     */
    private $_tree = null;

    /**
     * Default configuration
     *
     * @var array
     */
    private $_configuration = [
        'item_tag'           => 'li',
        'item_attributes'    => [],
        'sub_item_tag'        => 'ul',
        'sub_item_attributes' => [],
        'href_attributes'    => [],
        'active_class'       => 'active',
    ];

    /**
     * @param       $name
     * @param array $configuration
     */
    public function __construct($name, array $configuration = [])
    {
        $this->_name = (string) $name;
        $this->_configuration = array_merge($this->_configuration, (array) $configuration);
    }

    /**
     * Get configuration
     * s*
     *
     * @param null $config
     * @return array|null
     */
    public function getConfiguration($config = null)
    {
        if ($config === null)
        {
            return $this->_configuration;
        }

        return (isset($this->_configuration[$config])) ? $this->_configuration[$config] : null;
    }

    /**
     * 注册一个新的菜单项
     *
     * @param Item $item
     * @return bool
     */
    public function registerItem(Item $item)
    {
        return $this->_addItem($item);
    }

    /**
     * Un-register menu item
     *
     * @param $item
     * @return bool, true if success, otherwise false
     */
    public function removeItem($item)
    {

        if ($item instanceof Item)
        {
            $item = $item->getName();
        }

        if ($this->hasItem($item))
        {
            foreach ($this->_elements as $parent => $priority_list)
            {
                foreach ($priority_list as $priority => $elementsList)
                {
                    /** @var Item $element */
                    foreach ($elementsList as $key => $element)
                    {
                        if ($element->getName() === $item)
                        {
                            unset($this->_elements[$parent][$priority][$key]);
                            $this->_registeredElements = array_diff($this->_registeredElements, [$item]);
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Set active items
     *
     * @param      $item , null for no active items
     * @param bool $single
     * @return array
     */
    public function setActive($item, $single = true)
    {

        $single = (bool) $single;

        if ($item instanceof Item)
        {
            $item = $item->getName();
        }

        // Check if we have item like this one
        $item_exists = $this->hasItem($item);

        if ($item === null)
        {
            $this->_activeItems = [];
        }
        elseif ($single === true && $item_exists)
        {
            $this->_activeItems = [$item];
        }
        elseif ($item_exists)
        {
            $this->_activeItems[] = $item;
        }

        return $this->_activeItems;
    }

    /**
     * Check if item is active
     *
     * @param $item
     * @return bool
     */
    public function isActive($item)
    {

        if ($item instanceof Item)
        {
            $item = $item->getName();
        }

        return in_array($item, $this->_activeItems);
    }

    /**
     * Check if specific item is registered for menu
     *
     * @param $item
     * @return bool
     */
    public function hasItem($item)
    {

        if ($item instanceof Item)
        {
            return in_array($item->getName(), $this->_registeredElements);
        }

        return in_array($item, $this->_registeredElements);
    }

    /**
     * Build navigation tree
     */
    public function build()
    {
        $this->_tree = $this->buildLeaf(0);
        return $this->_tree;
    }

    /**
     * Build navigation leaf
     *
     * @param $position
     * @return array
     */
    public function buildLeaf($position)
    {
        $menuElements = isset($this->_elements[$position]) ? $this->_elements[$position] : [];
        ksort($menuElements);

        foreach ($menuElements as $items)
        {
            /** @var Item $item */
            foreach ($items as $item)
            {
                if ($this->isActive($item))
                {
                    $item->setActive();
                }

                /**
                 * Build child's
                 */
                if (isset($this->_elements[$item->getName()]))
                {
                    $item->setChildren($this->buildLeaf($item->getName()));
                }

            }
        }

        return $menuElements;
    }

    /**
     * Render this view
     *
     * @param array $attributes
     * @return string
     */
    public function render(array $attributes = [])
    {

        $this->build();
        $list = "";
        if ($this->_tree)
        {

            /**
             * Get list items attributes
             */
            $top_html_tag = (isset($attributes["sub_item_tag"])) ? $attributes["sub_item_tag"] : $this->getConfiguration('sub_item_tag');
            $top_html_attr = (isset($attributes["sub_item_attributes"])) ? $attributes["sub_item_attributes"] : $this->getConfiguration('sub_item_attributes');

            /**
             * Create list content
             */
            $list_content = $this->renderLeaf($this->_tree);

            /**
             * Create list
             */
            $html_attributes = "";
            foreach ($top_html_attr as $tag_name => $tag_value)
            {
                $html_attributes .= " {$tag_name}=\"{$tag_value}\"";
            }

            $list = "<{$top_html_tag}{$html_attributes}>{$list_content}</{$top_html_tag}>";
        }

        return $list;
    }

    /**
     * Render one leaf
     *
     * @param array $elements
     * @return string
     */
    public function renderLeaf(array $elements = [])
    {

        $return = "";

        foreach ($elements as $elementsLevel)
        {
            /** @var Item $element */
            foreach ($elementsLevel as $element)
            {

                $childrenTags = null;

                /**
                 * Get list items attributes
                 */
                $itemTag = ($element->getProperties('item_tag')) ? $element->getProperties('item_tag') : $this->getConfiguration('item_tag');
                $itemAttr = (array) ($element->getProperties('item_attributes')) ? $element->getProperties('item_attributes') : $this->getConfiguration('item_attributes');

                $subItemTag = ($element->getProperties('sub_item_tag')) ? $element->getProperties('sub_item_tag') : $this->getConfiguration('sub_item_tag');
                $subItemAttr = (array) ($element->getProperties('sub_item_attributes')) ? $element->getProperties('sub_item_attributes') : $this->getConfiguration('sub_item_attributes');

                $hrefAttr = (array) ($element->getProperties('href_attributes')) ? $element->getProperties('href_attributes') : $this->getConfiguration('href_attributes');

                if ($element->hasChildren())
                {
                    $childrenTags = $this->renderLeaf($element->getChildren());
                }

                if ($element->isActive())
                {
                    if (isset($itemAttr['class']))
                    {
                        $itemAttr['class'] .= ' ' . $this->getConfiguration('active_class');
                    }
                    else
                    {
                        $itemAttr['class'] = $this->getConfiguration('active_class');
                    }
                }

                $htmlAttributes = "";
                foreach ($hrefAttr as $tagName => $tagValue)
                {
                    $htmlAttributes .= " {$tagName}=\"{$tagValue}\"";
                }

                $thisTag = new A(array_merge(['href' => $element->getHref()], $hrefAttr), $element->getText());

                if (isset($childrenTags))
                {

                    $htmlAttributes = "";
                    foreach ($subItemAttr as $tagName => $tagValue)
                    {
                        $htmlAttributes .= " {$tagName}=\"{$tagValue}\"";
                    }

                    $thisTag .= "<{$subItemTag}{$htmlAttributes}>{$childrenTags}</{$subItemTag}>";
                }

                $htmlAttributes = "";
                foreach ($itemAttr as $tagName => $tagValue)
                {
                    $htmlAttributes .= " {$tagName}=\"{$tagValue}\"";
                }

                $return .= "<{$itemTag}{$htmlAttributes}>{$thisTag}</{$itemTag}>";
            }
        }

        return $return;
    }

    /**
     * Add item and item key to this navigation
     *
     * @param Item $item
     * @param null $parent
     * @param null $priority
     * @return bool
     * @throws Exception
     */
    private function _addItem(Item $item, $parent = null, $priority = null)
    {
        if ($this->hasItem($item))
        {
            throw new Exception("Navigation item with name {$item->getName()}, already exists.");
        }

        $parent = ($parent === null) ? $item->getParent() : $parent;
        $priority = (int) (($priority === null) ? $item->getPriority() : $priority);

        if ($parent !== 0 && ! $this->hasItem($parent))
        {
            throw new Exception("You are trying register item for not existing parent {$parent}.");
        }

        $this->_elements[$parent][$priority][] = $item;
        $this->_registeredElements[] = $item->getName();

        return true;
    }

}
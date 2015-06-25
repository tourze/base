<?php

namespace tourze\Html\Feature;

trait Base
{

    /**
     * @var  array  默认标签的排序方式
     */
    public static $attributeOrder = [
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxLength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabIndex',
        'accessKey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled',
        'body',
    ];

    /**
     * @var array 当前标签的属性值
     */
    protected $_attributes = [];

    /**
     * 读取指定属性值
     *
     * @param $name
     *
     * @return null|string|array
     */
    protected function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    /**
     * 设置属性值
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;

        return $this;
    }

    /**
     * 合并属性值
     *
     * @return string
     */
    protected function combineAttributes()
    {
        $attributes = $this->_attributes;

        // 对属性进行排序
        $sorted = [];
        foreach (self::$attributeOrder as $key)
        {
            if (isset($attributes[$key]))
            {
                $sorted[$key] = $attributes[$key];
            }
        }
        // 再合并
        $attributes = $sorted + $attributes;

        $compiled = '';
        foreach ($attributes as $key => $value)
        {
            if (null === $value)
            {
                // Skip attributes that have null values
                continue;
            }

            // Add the attribute key
            $compiled .= ' ' . strtolower($key);

            if ($value)
            {
                // Add the attribute value
                $compiled .= '="' . self::chars($value) . '"';
            }
        }

        return $compiled;
    }

    /**
     * Convert special characters to HTML entities. All untrusted content
     * should be passed through this method to prevent XSS injections.
     *     echo self::chars($username);
     *
     * @param   string  $value        string to convert
     * @param   boolean $doubleEncode encode existing entities
     *
     * @return  string
     */
    public static function chars($value, $doubleEncode = true)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'utf-8', $doubleEncode);
    }

}

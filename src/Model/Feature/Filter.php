<?php

namespace tourze\Model\Feature;

class Filter
{

    /**
     * 转换为布尔值
     *
     * @param $value
     * @return bool
     */
    public static function castToBoolean($value)
    {
        return (bool) $value;
    }

    /**
     * 转换为整形
     *
     * @param $value
     * @return int
     */
    public static function castToInteger($value)
    {
        return (int) $value;
    }

    /**
     * 转换为整形
     *
     * @param $value
     * @return int
     */
    public static function castToFloat($value)
    {
        return (float) $value;
    }
}

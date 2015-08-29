<?php

namespace tourze\Base;

class Security
{

    /**
     * 标准化过滤输入的数据，主要两个功能：
     *
     * - 安全过滤，对特殊字符进行转义
     * - 对换行符进行格式化
     *
     * @param  mixed $value 任意变量
     * @return mixed
     */
    public static function sanitize($value)
    {
        if (is_array($value) || is_object($value))
        {
            foreach ($value as $key => $val)
            {
                $value[$key] = self::sanitize($val);
            }
        }
        elseif (is_string($value))
        {
            if (true === Base::$magicQuotes)
            {
                $value = stripslashes($value);
            }
            if (false !== strpos($value, "\r"))
            {
                $value = str_replace([
                    "\r\n",
                    "\r"
                ], "\n", $value);
            }
        }

        return $value;
    }
}

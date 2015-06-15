<?php

namespace tourze\Base\Helper;

/**
 * DZ中很经典的加解密函数
 *
 * @package tourze\Base\Helper
 */
class AuthCode
{

    public static $key = 'tourze';

    /**
     * @param mixed $plain
     * @param string $key
     * @param int    $expire
     * @return string
     */
    public static function encode($plain, $key = '', $expire = 0)
    {
        return self::call($plain, 'ENCODE', $key, $expire);
    }

    /**
     * @param mixed $plain
     * @param string $key
     * @param int    $expire
     * @return string
     */
    public static function decode($plain, $key = '', $expire = 0)
    {
        return self::call($plain, 'DECODE', $key, $expire);
    }

    /**
     * @param string $input     要加密的字符串
     * @param string $operation 要加密还是解密
     * @param string $key       密钥
     * @param int    $expiry    过期时间
     * @return string
     */
    protected static function call($input, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $challengeKeyLength 次方
        // 当此值为 0 时，则不产生随机密钥
        $challengeKeyLength = 6;

        // 密匙
        $key = md5($key ? $key : self::$key);

        // 密匙a会参与加解密
        $keyA = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyB = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyC = $challengeKeyLength
            ? (
                $operation == 'DECODE'
                    ? substr($input, 0, $challengeKeyLength)
                    : substr(md5(microtime()), -$challengeKeyLength))
            : '';

        // 参与运算的密匙
        $cryptKey = $keyA . md5($keyA . $keyC);
        $keyLength = strlen($cryptKey);

        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $input = $operation == 'DECODE'
            ? base64_decode(substr($input, $challengeKeyLength))
            : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($input . $keyB), 0, 16) . $input;
        $strLength = strlen($input);
        $result = '';

        $box = range(0, 255);
        $randKeys = [];
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++)
        {
            $randKeys[$i] = ord($cryptKey[$i % $keyLength]);
        }

        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++)
        {
            $j = ($j + $box[$i] + $randKeys[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $strLength; $i++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($input[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE')
        {
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
            if (
                (substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)
                &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyB), 0, 16)
            )
            {
                return substr($result, 26);
            }
            else
            {
                return '';
            }
        }
        else
        {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyC . str_replace('=', '', base64_encode($result));
        }
    }

}

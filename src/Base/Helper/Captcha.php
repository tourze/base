<?php

namespace tourze\Base\Helper;

use tourze\Http\HttpRequest;

/**
 * 远程captcha验证
 *
 * @package uc
 */
class Captcha
{

    public static $captchaSignKey = 'captcha_session';

    /**
     * @var string 第三方资源地址
     */
    public static $captchaHost = null;

    /**
     * 读取第三方资源地址
     *
     * @return string
     */
    public static function captchaHost()
    {
        if (self::$captchaHost === null)
        {
            self::$captchaHost = 'http://captcha.tourze.com/';
            if (strpos($_SERVER['HTTP_HOST'], 'test.') !== false)
            {
                self::$captchaHost = 'http://test.captcha.tourze.com/';
            }
            if (strpos($_SERVER['HTTP_HOST'], 'local.') !== false)
            {
                self::$captchaHost = 'http://local.captcha.tourze.com/';
            }
        }

        return self::$captchaHost;
    }

    /**
     * 准备上下文信息
     *
     * @return array
     */
    public static function prepareContext()
    {
        return [
            'ip'   => HttpRequest::$clientIp,
            'ua'   => HttpRequest::$userAgent,
        ];
    }

    /**
     * 检验输入的验证码是否正确
     *
     * @param string $code
     * @param array  $context
     * @return bool
     */
    public static function valid($code, $context = null)
    {
        if ($context === null)
        {
            // 补充上下文，默认只有这两个三下文
            $context = self::prepareContext();
        }
        $context['code'] = $code;

        $request = HttpRequest::factory(self::captchaHost().'valid');
        $request->method = HttpRequest::POST;
        $request->post([
            'session' => isset($_COOKIE[self::$captchaSignKey]) ? $_COOKIE[self::$captchaSignKey] : '',
            'context' => json_encode($context),
        ]);
        $response = $request->execute();

        return $response->body == 1;
    }

}

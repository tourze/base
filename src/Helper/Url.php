<?php

namespace tourze\Base\Helper;

use tourze\Base\Exception\HelperException;
use tourze\Base\Base;

/**
 * URL助手类
 *
 * @package tourze\Base\Helper
 */
class Url extends HelperBase implements HelperInterface
{

    /**
     * 获取主请求的uri字符串，默认按照PATH_INFO、REQUEST_URI、PHP_SELF、REDIRECT_URL这样的顺序读取
     *
     *     $uri = Uri::detectUri();
     *
     * @return string URI
     * @throws HelperException
     */
    public static function detectUri()
    {
        // 如果PATH_INFO读不到，那么就从其他途径读
        if ( ! $uri = Arr::get($_SERVER, 'PATH_INFO'))
        {
            // REQUEST_URI和PHP_SELF会包含当前脚本路径
            if (isset($_SERVER['REQUEST_URI']))
            {
                $uri = $_SERVER['REQUEST_URI'];
                if ($requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
                {
                    $uri = $requestUri;
                }
                $uri = rawurldecode($uri);
            }
            elseif (isset($_SERVER['PHP_SELF']))
            {
                $uri = $_SERVER['PHP_SELF'];
            }
            elseif (isset($_SERVER['REDIRECT_URL']))
            {
                $uri = $_SERVER['REDIRECT_URL'];
            }
            else
            {
                throw new HelperException('Unable to detect the URI using PATH_INFO, REQUEST_URI, PHP_SELF or REDIRECT_URL');
            }

            $baseUrl = parse_url(Base::$baseUrl, PHP_URL_PATH);
            if (strpos($uri, $baseUrl) === 0)
            {
                $uri = (string) substr($uri, strlen($baseUrl));
            }
            if (Base::$indexFile && strpos($uri, Base::$indexFile) === 0)
            {
                $uri = (string) substr($uri, strlen(Base::$indexFile));
            }
        }

        return $uri;
    }

    /**
     * 获取当前应用的基础路径
     *
     *     // 返回不带主机名和协议的相对路径
     *     echo Url::base();
     *
     *     // 带主机名和指定协议的绝对路径
     *     echo Url::base('https', true);
     *
     * @param  string $protocol 协议字符串
     * @param  bool   $index    是否在链接中增加缺省文件
     * @return string
     */
    public static function base($protocol = null, $index = false)
    {
        $baseUrl = Base::$baseUrl;

        if ( ! $protocol)
        {
            $protocol = parse_url($baseUrl, PHP_URL_SCHEME);
        }

        if (true === $index && ! empty(Base::$indexFile))
        {
            $baseUrl .= Base::$indexFile . '/';
        }

        if (is_string($protocol))
        {
            if ($port = parse_url($baseUrl, PHP_URL_PORT))
            {
                // 找到端口
                $port = ':' . $port;
            }

            if ($domain = parse_url($baseUrl, PHP_URL_HOST))
            {
                // 移除除了路径外的所有信息
                $baseUrl = parse_url($baseUrl, PHP_URL_PATH);
            }
            else
            {
                $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            }

            $baseUrl = $protocol . '://' . $domain . $port . $baseUrl;
        }

        return $baseUrl;
    }

    /**
     * 返回一个绝对路径URL
     *
     *     echo Url::site('foo/bar');
     *
     * @param  string $uri      传入URI
     * @param  mixed  $protocol 协议字符串
     * @param  bool   $index    是否包含缺省文件
     * @return string
     */
    public static function site($uri = '', $protocol = null, $index = false)
    {
        // 关闭可能的计划，主机，端口，用户和其他匹配到的部分
        $path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));

        if (preg_match('/[^\x00-\x7F]/S', $path))
        {
            // 编码所有非ASCII字符，RFC 1738标准
            $path = preg_replace_callback('~([^/]+)~', 'rawurlencode', $path);
        }

        return self::base($protocol, $index) . $path;
    }

    /**
     * 返回指定变量的字符串格式
     *
     *     // 返回"?sort=title&limit=10"
     *     $query = Url::query(['sort' => 'title', 'limit' => 10]);
     *
     * [!!] 参数中带null的话，这个键会被忽略
     *
     * @param  array $params 参数列表Array of GET parameters
     * @param  bool  $useGet 是否合并当前的$_GET数组
     * @return string
     */
    public static function query(array $params = null, $useGet = false)
    {
        if ($useGet)
        {
            if (null === $params)
            {
                $params = $_GET;
            }
            else
            {
                $params = Arr::merge($_GET, $params);
            }
        }

        if (empty($params))
        {
            return '';
        }

        $query = http_build_query($params, '', '&');
        return ($query === '') ? '' : ('?' . $query);
    }
}

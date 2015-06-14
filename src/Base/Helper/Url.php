<?php

namespace tourze\Base\Helper;

use tourze\Base\Exception\HelperException;
use tourze\Base\Base;
use tourze\Http\HttpRequest;

/**
 * URL助手类.
 *
 * @package    Base
 * @category   Helpers
 * @author     YwiSax
 */
class Url
{

    /**
     * 获取主请求的uri字符串，默认按照PATH_INFO、REQUEST_URI、PHP_SELF、REDIRECT_URL这样的顺序读取
     *
     *     $uri = Request::detectUri();
     *
     * @return  string  URI
     * @throws  HelperException
     */
    public static function detectUri()
    {
        if ( ! empty($_SERVER['PATH_INFO']))
        {
            $uri = $_SERVER['PATH_INFO'];
        }
        else
        {
            // REQUEST_URI and PHP_SELF include the docroot and index
            if (isset($_SERVER['REQUEST_URI']))
            {
                /**
                 * We use REQUEST_URI as the fallback value. The reason
                 * for this is we might have a malformed URL such as:
                 *  http://localhost/http://example.com/judge.php
                 * which parse_url can't handle. So rather than leave empty
                 * handed, we'll use this.
                 */
                $uri = $_SERVER['REQUEST_URI'];
                if ($requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
                {
                    // Valid URL path found, set it.
                    $uri = $requestUri;
                }
                // Decode the request URI
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

            // Get the path from the base URL, including the index file
            $baseUrl = parse_url(Base::$baseUrl, PHP_URL_PATH);
            if (strpos($uri, $baseUrl) === 0)
            {
                // Remove the base URL from the URI
                $uri = (string) substr($uri, strlen($baseUrl));
            }
            if (Base::$indexFile && strpos($uri, Base::$indexFile) === 0)
            {
                // Remove the index file from the URI
                $uri = (string) substr($uri, strlen(Base::$indexFile));
            }
        }

        return $uri;
    }

    /**
     * Gets the base URL to the application.
     * To specify a protocol, provide the protocol as a string or request object.
     * If a protocol is used, a complete URL will be generated using the
     * `$_SERVER['HTTP_HOST']` variable.
     *
     *     // Absolute URL path with no host or protocol
     *     echo URL::base();
     *     // Absolute URL path with host, https protocol and index.php if set
     *     echo URL::base('https', true);
     *     // Absolute URL path with host and protocol from $request
     *     echo URL::base($request);
     *
     * @param   mixed   $protocol Protocol string, [Request], or boolean
     * @param   boolean $index    Add index file to URL?
     * @return  string
     */
    public static function base($protocol = null, $index = false)
    {
        // Start with the configured base URL
        $baseUrl = Base::$baseUrl;

        if (true === $protocol)
        {
            // Use the initial request to get the protocol
            $protocol = HttpRequest::$initial;
        }

        if ($protocol instanceof HttpRequest)
        {
            if ( ! $protocol->secure)
            {
                // Use the current protocol
                list($protocol) = explode('/', strtolower($protocol->protocol));
            }
            else
            {
                $protocol = 'https';
            }
        }

        if ( ! $protocol)
        {
            // Use the configured default protocol
            $protocol = parse_url($baseUrl, PHP_URL_SCHEME);
        }

        if (true === $index && ! empty(Base::$indexFile))
        {
            // Add the index file to the URL
            $baseUrl .= Base::$indexFile . '/';
        }

        if (is_string($protocol))
        {
            if ($port = parse_url($baseUrl, PHP_URL_PORT))
            {
                // Found a port, make it usable for the URL
                $port = ':' . $port;
            }

            if ($domain = parse_url($baseUrl, PHP_URL_HOST))
            {
                // Remove everything but the path from the URL
                $baseUrl = parse_url($baseUrl, PHP_URL_PATH);
            }
            else
            {
                // Attempt to use HTTP_HOST and fallback to SERVER_NAME
                $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            }

            // Add the protocol and domain to the base URL
            $baseUrl = $protocol . '://' . $domain . $port . $baseUrl;
        }

        return $baseUrl;
    }

    /**
     * Fetches an absolute site URL based on a URI segment.
     *     echo URL::site('foo/bar');
     *
     * @param   string  $uri      Site URI to convert
     * @param   mixed   $protocol Protocol string or [Request] class to use protocol from
     * @param   boolean $index    Include the index_page in the URL
     *
     * @return  string
     * @uses    URL::base
     */
    public static function site($uri = '', $protocol = null, $index = true)
    {
        // Chop off possible scheme, host, port, user and pass parts
        $path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));

        if (preg_match('/[^\x00-\x7F]/S', $path))
        {
            // Encode all non-ASCII characters, as per RFC 1738
            $path = preg_replace_callback('~([^/]+)~', 'rawurlencode', $path);
        }

        // Concat the URL
        return self::base($protocol, $index) . $path;
    }

    /**
     * Merges the current GET parameters with an array of new or overloaded
     * parameters and returns the resulting query string.
     *
     *     // Returns "?sort=title&limit=10" combined with any existing GET values
     *     $query = URL::query(['sort' => 'title', 'limit' => 10]);
     *
     * Typically you would use this when you are sorting query results,
     * or something similar.
     * [!!] Parameters with a null value are left out.
     *
     * @param   array   $params Array of GET parameters
     * @param   boolean $useGet Include current request GET parameters
     *
     * @return  string
     */
    public static function query(array $params = null, $useGet = true)
    {
        if ($useGet)
        {
            if (null === $params)
            {
                // Use only the current parameters
                $params = $_GET;
            }
            else
            {
                // Merge the current and new parameters
                $params = Arr::merge($_GET, $params);
            }
        }

        if (empty($params))
        {
            // No query parameters
            return '';
        }

        // Note: http_build_query returns an empty string for a params array with only null values
        $query = http_build_query($params, '', '&');

        // Don't prepend '?' to an empty string
        return ($query === '') ? '' : ('?' . $query);
    }
}

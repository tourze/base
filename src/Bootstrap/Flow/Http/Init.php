<?php

namespace tourze\Bootstrap\Flow\Http;

use tourze\Flow\HandlerInterface;
use tourze\Flow\Layer;
use tourze\Base\Helper\Cookie;
use tourze\Http\Http;
use tourze\Http\Request;

/**
 * HTTP初始化
 *
 * @package tourze\Mvc\Flow
 */
class Init extends Layer implements HandlerInterface
{

    /**
     * 每个请求层，最终被调用的方法
     *
     * @return mixed
     */
    public function handle()
    {
        // 决定当前使用的协议版本
        if (isset($_SERVER['SERVER_PROTOCOL']))
        {
            Http::$protocol = $_SERVER['SERVER_PROTOCOL'];
        }

        /** @var Request $request */
        $request =& $this->flow->contexts['request'];
        //var_dump($request->isInitial());
        //echo "httpInit1:" . spl_object_hash($request) . "<br>\n";

        // 如果当前请求是初始请求，那么对其进行额外处理
        if ($request->isInitial())
        {
            if (
                ( ! empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN))
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'])
                && in_array($_SERVER['REMOTE_ADDR'], Request::$trustedProxies)
            )
            {
                $request->secure = true;
            }

            $protocol = Http::$protocol;

            if (isset($_SERVER['REQUEST_METHOD']))
            {
                $method = $_SERVER['REQUEST_METHOD'];
            }
            else
            {
                $method = Request::GET;
            }

            if (isset($_SERVER['HTTP_REFERER']))
            {
                // There is a referrer for this request
                $referrer = $_SERVER['HTTP_REFERER'];
            }

            if (isset($_SERVER['HTTP_USER_AGENT']))
            {
                // Browser type
                Request::$userAgent = $_SERVER['HTTP_USER_AGENT'];
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
            {
                // Typically used to denote AJAX requests
                $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'];
            }

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                && isset($_SERVER['REMOTE_ADDR'])
                && in_array($_SERVER['REMOTE_ADDR'], Request::$trustedProxies)
            )
            {
                // Use the forwarded IP address, typically set when the
                // client is using a proxy server.
                // Format: "X-Forwarded-For: client1, proxy1, proxy2"
                $clientIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                Request::$clientIp = array_shift($clientIps);

                unset($clientIps);
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP'])
                && isset($_SERVER['REMOTE_ADDR'])
                && in_array($_SERVER['REMOTE_ADDR'], Request::$trustedProxies)
            )
            {
                // Use the forwarded IP address, typically set when the
                // client is using a proxy server.
                $clientIps = explode(',', $_SERVER['HTTP_CLIENT_IP']);

                Request::$clientIp = array_shift($clientIps);

                unset($clientIps);
            }
            elseif (isset($_SERVER['REMOTE_ADDR']))
            {
                // The remote IP address
                Request::$clientIp = $_SERVER['REMOTE_ADDR'];
            }

            if ($method !== Request::GET)
            {
                // Ensure the raw body is saved for future use
                $body = file_get_contents('php://input');
            }

            $cookies = [];
            if (($cookieKeys = array_keys($_COOKIE)))
            {
                foreach ($cookieKeys as $key)
                {
                    $cookies[$key] = Cookie::get($key);
                }
            }

            // Store global GET and POST data in the initial request only
            $request->protocol = $protocol;
            $request
                ->query($_GET)
                ->post($_POST);

            if (isset($method))
            {
                // Set the request method
                $request->method = $method;
            }
            if (isset($referrer))
            {
                // Set the referrer
                $request->referrer = $referrer;
            }
            if (isset($requestedWith))
            {
                // Apply the requested with variable
                $request->requestedWith = $requestedWith;
            }
            if (isset($body))
            {
                // Set the request body (probably a PUT type)
                $request->body = $body;
            }
            if (isset($cookies))
            {
                $request->cookie($cookies);
            }

            //var_dump($request->query());
            //var_dump($request->isInitial());
            //echo "httpInit2:" . spl_object_hash($request) . "<br>\n";
            //$this->flow->contexts['request'] = $request;
        }
    }
}

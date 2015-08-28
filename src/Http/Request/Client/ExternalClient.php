<?php

namespace tourze\Http\Request\Client;

use Exception;
use Requests;
use tourze\Base\Base;
use tourze\Http\Response;
use tourze\Http\Request;
use tourze\Http\Request\Exception\RequestException;
use tourze\Http\Request\RequestClient;

/**
 * 获取指定地址的外部请求
 *
 * @package tourze\Http\Request\Client
 */
class ExternalClient extends RequestClient
{

    /**
     * 执行外部请求，并返回接口
     *
     *     $request->execute();
     *
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     * @throws Exception
     */
    public function executeRequest(Request $request, Response $response)
    {
        $previous = Request::$current;
        Request::$current = $request;

        // 如果post数据了
        if ($post = $request->post())
        {
            $request->body = http_build_query($post, null, '&');
            $request->headers('content-type', 'application/x-www-form-urlencoded; charset=' . Base::$charset);
        }

        // 如果需要暴露框架信息
        if (Base::$expose)
        {
            $request->headers('user-agent', Base::version());
        }

        try
        {
            // 处理header
            $sendHeaders = [];
            if ($headers = $request->headers())
            {
                foreach ($headers as $key => $value)
                {
                    if (is_array($value))
                    {
                        $value = implode(', ', $value);
                    }
                    $sendHeaders[$key] = $value;
                }
            }

            // 处理cookie
            if ($cookies = $request->cookie())
            {
                $sendHeaders['Cookie'] = http_build_query($cookies, null, '; ');
            }

            $url = $request->uri;
            if ($query = $request->query())
            {
                $url .= '?' . http_build_query($query, null, '&');
            }

            // 执行请求
            $result = Requests::request($url, $sendHeaders, $request->body, $request->method);
            if ( ! $result->success)
            {
                throw new RequestException('Error fetching remote :url [ status :code ]', [
                    ':url'   => $url,
                    ':code'  => $result->status_code,
                ]);
            }

            foreach ($result->headers as $k => $v)
            {
                $response->headers($k, $v);
            }
            $response->status = $result->status_code;
            $response->body = $result->body;
        }
        catch (Exception $e)
        {
            Request::$current = $previous;
            throw $e;
        }

        Request::$current = $previous;
        return $response;
    }
}

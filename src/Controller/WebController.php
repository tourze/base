<?php

namespace tourze\Controller;

use tourze\Http\Response;
use tourze\Http\Http;
use tourze\Http\Exception\HttpException;

/**
 * 最基础的Web控制器
 *
 * @package tourze\Controller
 */
abstract class WebController extends Controller
{

    /**
     * 跳转的助手方法
     *
     * @param  string $uri  要跳转的URI
     * @param  int    $code HTTP状态码
     * @throws HttpException
     */
    public function redirect($uri = '', $code = 302)
    {
        Http::redirect((string) $uri, $code);
    }

    /**
     * 检测请求缓存
     *
     *     $this->checkCache(sha1($content));
     *
     * @param  string $etag Resource Etag
     * @return Response
     */
    protected function checkCache($etag = null)
    {
        return Http::checkCache($this->request, $this->response, $etag);
    }
}

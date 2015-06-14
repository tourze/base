<?php

namespace tourze\Controller;

use tourze\Http\HttpResponse;
use tourze\Http\Http;
use tourze\Http\Exception\HttpException;

abstract class BaseController extends Controller
{

    /**
     * 跳转的助手方法
     *
     * @param  string $uri  URI to redirect to
     * @param  int    $code HTTP Status code to use for the redirect
     * @throws HttpException
     */
    public function redirect($uri = '', $code = 302)
    {
        Http::redirect((string) $uri, $code);
    }

    /**
     * Checks the browser cache to see the response needs to be returned,
     * execution will halt and a 304 Not Modified will be sent if the
     * browser cache is up to date.
     *     $this->checkCache(sha1($content));
     *
     * @param  string $etag Resource Etag
     * @return HttpResponse
     */
    protected function checkCache($etag = null)
    {
        return Http::checkCache($this->request, $this->response, $etag);
    }
}

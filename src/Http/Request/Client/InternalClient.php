<?php

namespace tourze\Http\Request\Client;

use ReflectionClass;
use tourze\Base\Exception\BaseException;
use tourze\Http\Exception\HttpException;
use tourze\Http\Message;
use tourze\Http\Response;
use tourze\Http\Request;
use tourze\Http\Request\RequestClient;

/**
 * 内部执行的请求
 *
 * @package tourze\Http\Request\Client
 */
class InternalClient extends RequestClient
{

    /**
     * 处理请求
     *
     *     $request->execute();
     *
     * @param   Request  $request
     * @param   Response $response
     * @return \tourze\Http\Response
     * @throws \tourze\Base\Exception\BaseException
     */
    public function executeRequest(Request $request, Response $response)
    {
        $className = 'Controller';

        // 控制器
        $controller = $request->controller;
        $className = $controller . $className;

        // 目录
        $directory = $request->directory;
        if ($directory)
        {
            $directory = str_replace('/', '\\', $directory);
            $className = $directory . $className;
        }

        // 保存请求状态
        $previous = Request::$current;
        Request::$current = $request;

        try
        {
            if ( ! class_exists($className))
            {
                throw HttpException::factory(Message::NOT_FOUND, 'The requested URL :uri was not found on this server.', [
                    ':uri' => $request->uri
                ])->request($request);
            }

            $class = new ReflectionClass($className);

            if ($class->isAbstract())
            {
                throw new BaseException('Cannot create instances of abstract :controller', [
                    ':controller' => $className
                ]);
            }

            $controller = $class->newInstance([
                'request'  => $request,
                'response' => $response,
            ]);
            $response = $class->getMethod('execute')->invoke($controller);

            if ( ! $response instanceof Response)
            {
                throw new BaseException('Controller failed to return a Response');
            }
        }
        catch (HttpException $e)
        {
            if (null === $e->request())
            {
                $e->request($request);
            }
            $response = $e->getResponse();
        }

        Request::$current = $previous;

        return $response;
    }

}

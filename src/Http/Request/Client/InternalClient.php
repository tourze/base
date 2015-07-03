<?php

namespace tourze\Http\Request\Client;

use ReflectionClass;
use tourze\Base\Exception\BaseException;
use tourze\Http\Exception\HttpException;
use tourze\Http\Response;
use tourze\Http\Request;
use tourze\Http\Request\RequestClient;

/**
 * 内部执行的请求
 *
 * @package    Base
 * @category   Base
 * @author     YwiSax
 */
class InternalClient extends RequestClient
{

    /**
     * @var    array
     */
    protected $_previousEnvironment;

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

        // 保存状态
        $previous = Request::$current;
        Request::$current = $request;

        try
        {
            if ( ! class_exists($className))
            {
                throw HttpException::factory(404, 'The requested URL :uri was not found on this server.', [
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
                // Controller failed to return a Response.
                throw new BaseException('Controller failed to return a Response');
            }
        }
        catch (HttpException $e)
        {
            // Store the request context in the Exception
            if (null === $e->request())
            {
                $e->request($request);
            }

            // Get the response via the Exception
            $response = $e->getResponse();
        }

        // Restore the previous request
        Request::$current = $previous;

        return $response;
    }

}

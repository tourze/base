<?php

namespace tourze\Bootstrap\Flow;

use tourze\Base\I18n;
use tourze\Flow\HandlerInterface;
use tourze\Flow\Layer;
use tourze\Base\Helper\Url;
use tourze\Route\Exception\RouteNotFoundException;
use tourze\Route\Route;

/**
 * SDK框架执行流
 *
 * @package tourze\Mvc\Flow
 */
class Base extends Layer implements HandlerInterface
{

    /**
     * 每个请求层，最终被调用的方法
     *
     * @return mixed
     */
    public function handle()
    {
        I18n::lang('zh-cn');

        Route::$lowerUri = true;

        try
        {
            Route::get('default');
        }
        catch (RouteNotFoundException $e)
        {
            Route::set('default', '(<controller>(/<action>(/<id>)))')
                ->defaults([
                    'controller' => 'Site',
                    'action'     => 'index',
                ]);
        }

        $this->flow->contexts['uri'] = Url::detectUri();
    }
}

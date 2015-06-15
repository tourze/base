<?php

namespace tourze\Base;

use tourze\Base\Exception\RouteNotFoundException;
use tourze\Base\Flow\FlowHandlerInterface;
use tourze\Base\Flow\FlowLayer;
use tourze\Base\Helper\Url;

/**
 * SDK框架执行流
 *
 * @package tourze\Mvc\Flow
 */
class BaseFlow extends FlowLayer implements FlowHandlerInterface
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

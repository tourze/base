<?php

namespace tourze\Bootstrap;

/**
 * 初始化
 *
 * @package tourze\Bootstrap
 */
class Bootstrap
{

    /**
     * @var array SDK工作流分层
     */
    public static $layers = [
        'tourze\Bootstrap\Flow\Base',  // SDK基础工作层
        'tourze\Bootstrap\Flow\Http', // 执行HTTP相关控制
    ];

    /**
     * 提交layer
     *
     * @param string $layer
     */
    public static function pushLayer($layer)
    {
        self::$layers[] = $layer;
    }
}

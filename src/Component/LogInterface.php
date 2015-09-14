<?php

namespace tourze\Base\Component;

use tourze\Base\ComponentInterface;

/**
 * 日志处理接口
 *
 * @package tourze\Base\Component
 */
interface LogInterface extends ComponentInterface
{

    /**
     * 调试信息
     *
     * @param string $log
     * @param array  $context
     */
    public function debug($log, array $context = []);

    /**
     * Info级别
     *
     * @param string $log
     * @param array  $context
     */
    public function info($log, array $context = []);

    /**
     * Notice级别
     *
     * @param string $log
     * @param array  $context
     */
    public function notice($log, array $context = []);

    /**
     * 警告信息
     *
     * @param string $log
     * @param array  $context
     */
    public function warning($log, array $context = []);

    /**
     * 错误信息
     *
     * @param string $log
     * @param array  $context
     */
    public function error($log, array $context = []);

    /**
     * critical级别
     *
     * @param string $log
     * @param array  $context
     */
    public function critical($log, array $context = []);

    /**
     * alert级别
     *
     * @param string $log
     * @param array  $context
     */
    public function alert($log, array $context = []);

    /**
     * emergency级别
     *
     * @param string $log
     * @param array  $context
     */
    public function emergency($log, array $context = []);
}

<?php

namespace tourze\Base\Component;

use tourze\Base\Component;

/**
 * 日志记录组件
 *
 * @package tourze\Base\Component
 */
class Log extends Component
{

    /**
     * @var array
     */
    public $logs = [
        'debug'     => [],
        'info'      => [],
        'notice'    => [],
        'warning'   => [],
        'error'     => [],
        'critical'  => [],
        'alert'     => [],
        'emergency' => [],
    ];

    /**
     * 调试信息
     *
     * @param string $log
     * @param array  $context
     */
    public function debug($log, array $context = [])
    {
        $this->logs['debug'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * Info级别
     *
     * @param string $log
     * @param array  $context
     */
    public function info($log, array $context = [])
    {
        $this->logs['info'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * Notice级别
     *
     * @param string $log
     * @param array  $context
     */
    public function notice($log, array $context = [])
    {
        $this->logs['notice'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * 警告信息
     *
     * @param string $log
     * @param array  $context
     */
    public function warning($log, array $context = [])
    {
        $this->logs['warning'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * 错误信息
     *
     * @param string $log
     * @param array  $context
     */
    public function error($log, array $context = [])
    {
        $this->logs['error'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * critical级别
     *
     * @param string $log
     * @param array  $context
     */
    public function critical($log, array $context = [])
    {
        $this->logs['critical'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * alert级别
     *
     * @param string $log
     * @param array  $context
     */
    public function alert($log, array $context = [])
    {
        $this->logs['alert'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * emergency级别
     *
     * @param string $log
     * @param array  $context
     */
    public function emergency($log, array $context = [])
    {
        $this->logs['emergency'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }
}

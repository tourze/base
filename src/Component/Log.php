<?php

namespace tourze\Base\Component;

use tourze\Base\Component;

/**
 * 日志记录组件
 * [!!] 此LOG组件对自身操作不做记录
 *
 * @package tourze\Base\Component
 */
class Log extends Component implements LogInterface
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
     * {@inheritdoc}
     */
    public function debug($log, array $context = [])
    {
        $this->logs['debug'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function info($log, array $context = [])
    {
        $this->logs['info'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function notice($log, array $context = [])
    {
        $this->logs['notice'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function warning($log, array $context = [])
    {
        $this->logs['warning'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function error($log, array $context = [])
    {
        $this->logs['error'][] = [
            'log'     => $log,
            'context' => $context,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function critical($log, array $context = [])
    {
        $this->logs['critical'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function alert($log, array $context = [])
    {
        $this->logs['alert'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($log, array $context = [])
    {
        $this->logs['emergency'][] = [
            'log'       => $log,
            'context'   => $context,
            'timestamp' => time(),
        ];
    }
}

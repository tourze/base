<?php

namespace tourze\Controller;

/**
 * 基于文本流的控制器
 *
 * @package tourze\Controller
 */
abstract class TextController extends Controller
{

    /**
     * 对应[Worker::$onWorkerStart]这个方法
     * 设置Worker启动时的回调函数，即当Worker启动后立即执行Worker::onWorkerStart成员指定的回调函数
     *
     * @see http://doc3.workerman.net/worker-development/on_worker_start.html
     * @return mixed
     */
    abstract public function actionStart();

    /**
     * 对应[Worker::$onWorkerStop]
     * 设置Worker停止时的回调函数，即当Worker收到stop信号后执行Worker::onWorkerStop指定的回调函数
     *
     * @see http://doc3.workerman.net/worker-development/on-worker-stop.html
     * @return mixed
     */
    abstract public function actionStop();

    /**
     * 对应[Worker::$onConnect]
     * 当有客户端连接时触发的回调函数
     *
     * @see http://doc3.workerman.net/worker-development/on-connect.html
     * @return mixed
     */
    abstract public function actionConnect();

    /**
     * 对应[Worker::$onMessage]
     * 当有客户端的连接上有数据发来时触发
     *
     * @see http://doc3.workerman.net/worker-development/on-message.html
     * @return mixed
     */
    abstract public function actionMessage();

    /**
     * 对应[Worker::$onClose]
     * 当客户端的连接断开时触发，不管连接是如何断开的，只要断开就会触发
     *
     * @see http://doc3.workerman.net/worker-development/on-close.html
     * @return mixed
     */
    abstract public function actionClose();

    /**
     * 对应[Worker::$onBufferFull]
     *
     * @see http://doc3.workerman.net/worker-development/on-buffer-full.html
     * @return mixed
     */
    abstract public function actionBufferFull();

    /**
     * 对应[Worker::$onBufferDrain]
     *
     * @see http://doc3.workerman.net/worker-development/on-buffer-drain.html
     * @return mixed
     */
    abstract public function actionBufferDrain();

    /**
     * 对应[Worker::$onError]
     * 当客户端的连接上发生错误时触发
     *
     * @see http://doc3.workerman.net/worker-development/on-error.html
     * @return mixed
     */
    abstract public function actionError();
}

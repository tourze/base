<?php

namespace tourze\Controller;

/**
 * REST控制器
 *
 * @package tourze\Controller
 */
abstract class RestController extends WebController
{

    /**
     * GET请求
     *
     * @return mixed
     */
    abstract public function actionGet();

    /**
     * POST请求
     *
     * @return mixed
     */
    abstract public function actionPost();

    /**
     * PUT请求
     *
     * @return mixed
     */
    abstract public function actionPut();

    /**
     * DELETE请求
     *
     * @return mixed
     */
    abstract public function actionDelete();

}

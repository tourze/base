<?php

namespace tourze\Base\Exception;

use Exception;
use tourze\Base\Base;

/**
 * 最基础的异常类，使用[I18n]来做异常信息的翻译
 *
 * @package tourze\Base\Exception
 */
class BaseException extends Exception implements ExceptionInterface
{

    /**
     * @var array 错误代码列表
     */
    public static $phpErrors = [
        E_ERROR             => 'Fatal Error',
        E_USER_ERROR        => 'User Error',
        E_PARSE             => 'Parse Error',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'User Warning',
        E_STRICT            => 'Strict',
        E_NOTICE            => 'Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
    ];

    /**
     * 创建一个新的异常实例
     *
     *     throw new BaseException('Something went terrible wrong, :user', [
     *         ':user' => $user
     *     ]);
     *
     * @param   string    $message   错误信息
     * @param   array     $variables 用于翻译的变量
     * @param   int       $code      异常代码
     * @param   Exception $previous  上一次异常
     */
    public function __construct($message = "", array $variables = null, $code = 0, Exception $previous = null)
    {
        $message = __($message, $variables);
        parent::__construct($message, (int) $code, $previous);
        $this->code = $code;
    }

    /**
     * 输出异常文本
     *
     *     echo $exception;
     *
     * @return string
     */
    public function __toString()
    {
        return self::text($this);
    }

    /**
     * 记录异常信息
     *
     * @param Exception $e
     */
    public static function log(Exception $e)
    {
        Base::getLog()->error($e->getMessage(), [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    /**
     * 获取最简单的异常信息
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param  Exception $e
     * @return string
     */
    public static function text(Exception $e)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]', get_class($e), $e->getCode(), strip_tags($e->getMessage()), $e->getFile(), $e->getLine());
    }

    /**
     * 获取异常响应对象
     *
     * @param Exception $e
     * @throws Exception
     */
    public static function response(Exception $e)
    {
        throw $e;
    }

}

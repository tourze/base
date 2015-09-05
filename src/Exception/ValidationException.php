<?php

namespace tourze\Base\Exception;

use Exception;
use tourze\Base\Security\Validation;

/**
 * 校验异常
 *
 * @package tourze\Base\Exception
 */
class ValidationException extends BaseException
{

    /**
     * @var object Validation实例
     */
    public $array;

    /**
     * @param Validation $array   Validation对象
     * @param string     $message 错误信息
     * @param array      $values  翻译变量
     * @param int        $code    异常代码
     * @param Exception  $previous
     */
    public function __construct(Validation $array, $message = 'Failed to validate array', array $values = null, $code = 0, Exception $previous = null)
    {
        $this->array = $array;

        parent::__construct($message, $values, $code, $previous);
    }

}

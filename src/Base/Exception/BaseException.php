<?php

namespace tourze\Base\Exception;

use Exception;
use tourze\Base\Debug;
use tourze\Base\Log;

/**
 * 最基础的异常类。 Translates exceptions using the [I18n] class.
 *
 * @package    Base
 * @category   Exceptions
 * @author     YwiSax
 */
class BaseException extends Exception implements ExceptionInterface
{

    /**
     * @var  array  PHP error code => human readable name
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
     * Creates a new translated exception.
     *     throw new BaseException('Something went terrible wrong, :user',
     *         [':user' => $user]);
     *
     * @param   string         $message   error message
     * @param   array          $variables translation variables
     * @param   integer|string $code      the exception code
     * @param   Exception      $previous  Previous exception
     */
    public function __construct($message = "", array $variables = null, $code = 0, Exception $previous = null)
    {
        $message = __($message, $variables);
        parent::__construct($message, (int) $code, $previous);
        $this->code = $code;
    }

    /**
     * Magic object-to-string method.
     *     echo $exception;
     *
     * @uses    BaseException::text
     * @return  string
     */
    public function __toString()
    {
        return self::text($this);
    }

    /**
     * 记录异常信息
     *
     * @param   Exception $e
     */
    public static function log(Exception $e)
    {
        $error = self::text($e);
        Log::error($error);
    }

    /**
     * Get a single line of text representing the exception:
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   Exception $e
     *
     * @return  string
     */
    public static function text(Exception $e)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), $e->getFile(), $e->getLine());
    }

    /**
     * Get a Response object representing the exception
     *
     * @param \Exception $e
     * @throws \Exception
     */
    public static function response(Exception $e)
    {
        throw $e;
    }

}

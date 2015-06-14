<?php

namespace tourze\Base\Exception;

use Exception;
use tourze\Base\Security\Validation;

/**
 * @package    Base
 * @category   Exceptions
 * @author     YwiSax
 */
class ValidationException extends BaseException
{

    /**
     * @var  object  Validation instance
     */
    public $array;

    /**
     * @param  Validation $array   Validation object
     * @param  string     $message error message
     * @param  array      $values  translation variables
     * @param  int        $code    the exception code
     * @param Exception   $previous
     */
    public function __construct(Validation $array, $message = 'Failed to validate array', array $values = null, $code = 0, Exception $previous = null)
    {
        $this->array = $array;

        parent::__construct($message, $values, $code, $previous);
    }

}

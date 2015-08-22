<?php

namespace tourze\Controller\Exception;

/**
 * JSONP中缺失callback参数会抛出这个异常
 *
 * @package tourze\Controller\Exception
 */
class JsonpInvalidParameterException extends ControllerException
{
}

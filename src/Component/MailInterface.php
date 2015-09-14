<?php

namespace tourze\Base\Component;

use tourze\Base\ComponentInterface;

/**
 * 邮件处理接口
 *
 * @package tourze\Base\Component
 */
interface MailInterface extends ComponentInterface
{

    /**
     * 发送邮件
     *
     * @param string|null  $from
     * @param string|array $to
     * @param string       $subject
     * @param string       $message
     * @return bool
     */
    public function send($from = null, $to = null, $subject = null, $message = null);
}

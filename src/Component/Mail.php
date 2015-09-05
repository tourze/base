<?php

namespace tourze\Base\Component;

use tourze\Base\Base;
use tourze\Base\Component;

/**
 * 默认的邮件组件，使用php的mail函数来发送
 *
 * @property string       from
 * @property string|array to
 * @property string       subject
 * @property string       message
 * @package tourze\Base\Component
 */
class Mail extends Component
{

    /**
     * @var string|array 收信人
     */
    protected $_to;

    /**
     * @return array|string
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * @param array|string $to
     */
    public function setTo($to)
    {
        $this->_to = $to;
    }

    /**
     * @var string 发件人
     */
    protected $_from;

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->_from = $from;
    }

    /**
     * @var string 邮件主题
     */
    protected $_subject;

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }

    /**
     * @var string 邮件内容
     */
    protected $_message;

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * 发送邮件
     *
     * @param string|array $to
     * @param string       $subject
     * @param string       $message
     * @param string|null  $from
     * @return bool
     */
    public function send($to = null, $subject = null, $message = null, $from = null)
    {
        Base::getLog()->debug(__METHOD__ . ' call send mail method', [
            'to'      => $to,
            'subject' => $subject,
            'message' => $message,
            'from'    => $from,
        ]);

        if ($to === null)
        {
            $to = $this->to;
        }
        if ($subject === null)
        {
            $subject = $this->subject;
        }
        if ($message === null)
        {
            $message = $this->message;
        }
        if ($from === null)
        {
            $from = $this->from;
        }

        if (is_array($to))
        {
            $to = implode(', ', $to);
        }

        Base::getLog()->debug(__METHOD__ . ' prepare to send mail', [
            'to'      => $to,
            'subject' => $subject,
            'message' => $message,
            'from'    => $from,
        ]);

        $result = @mail($to, $subject, $message, 'From: ' . $from);
        Base::getLog()->debug(__METHOD__ . ' send mail result', [
            'result' => $result,
        ]);

        $this->to = null;
        $this->subject = null;
        $this->message = null;
        $this->from = null;

        return $result;
    }
}

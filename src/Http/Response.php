<?php

namespace tourze\Http;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use tourze\Base\Helper\Mime;
use tourze\Base\Log;
use tourze\Base\Object;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Cookie;
use tourze\Base\Base;
use tourze\Http\Request\Exception\RequestException;

/**
 * 请求响应对象
 *
 * @package tourze\Http
 * @property   Message                                  message
 * @property   int                                      status
 * @property   int                                      contentLength
 * @property   string                                   protocol
 * @property   \Psr\Http\Message\StreamInterface|string body
 */
class Response extends Object implements ResponseInterface
{

    /**
     * 创建一个响应对象
     *
     * @param  array $config 配置信息
     * @return $this
     */
    public static function factory(array $config = [])
    {
        return new Response($config);
    }

    /**
     * @var int The response http status
     */
    protected $_status = 200;

    /**
     * @var Message
     */
    protected $_message = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->message = new Message;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return $this->message->getHeader($name);
    }

    /**
     * @param Message $message
     * @return Response
     */
    public function setMessage($message)
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->_message;
    }

    protected function getStatus()
    {
        return $this->_status;
    }

    protected function setStatus($status)
    {
        if (array_key_exists($status, Message::$text))
        {
            $this->_status = (int) $status;
        }
        else
        {
            throw new BaseException(__METHOD__ . ' unknown status value : :value', [':value' => $status]);
        }
    }

    /**
     * 读取响应内容
     *
     * @return \Psr\Http\Message\StreamInterface|string
     */
    public function getBody()
    {
        return $this->message->body;
    }

    /**
     * 设置响应内容
     *
     * @param \Psr\Http\Message\StreamInterface|string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->message->body = $body;
        return $this;
    }

    /**
     * @var  array       Cookies to be returned in the response
     */
    protected $_cookies = [];

    /**
     * @var  string      返回的协议字符串
     */
    protected $_protocol;

    protected function getProtocol()
    {
        if (null === $this->_protocol)
        {
            $this->_protocol = Http::$protocol;
        }

        return $this->_protocol;
    }

    protected function setProtocol($protocol)
    {
        $this->_protocol = strtoupper($protocol);
    }

    /**
     * 保存当前的response
     */
    public static $current = null;

    /**
     * 输出网页内容
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * 输出header信息
     *
     *       // 获取指定头信息
     *       $accept = $response->headers('Content-Type');
     *       // 设置头信息
     *       $response->headers('Content-Type', 'text/html');
     *       // 获取所有头信息
     *       $headers = $response->headers();
     *       // 一次设置多个头信息
     *       $response->headers(['Content-Type' => 'text/html', 'Cache-Control' => 'no-cache']);
     *
     * @param mixed  $name
     * @param string $value
     * @return mixed
     */
    public function headers($name = null, $value = null)
    {
        if (is_array($name))
        {
            foreach ($name as $k => $v)
            {
                $this->message->withHeader($k, $v);
            }
            return $this;
        }

        if (null === $name)
        {
            return $this->message->getHeaders();
        }
        elseif (null === $value)
        {
            return $this->message->getHeaderLine($name);
        }

        $this->message->withHeader($name, $value);

        return $this;
    }

    /**
     * Returns the length of the body for use with content header
     *
     * @return  int
     */
    public function getContentLength()
    {
        return strlen($this->body);
    }

    /**
     * 设置或者读取cookie
     *
     *     // Get the cookies set to the response
     *     $cookies = $response->cookie();
     *     // Set a cookie to the response
     *     $response->cookie('session', [
     *          'value' => $value,
     *          'expiration' => 12352234
     *     ]);
     *
     * @param  mixed  $key   cookie name, or array of cookie values
     * @param  string $value value to set to cookie
     * @return $this|mixed
     */
    public function cookie($key = null, $value = null)
    {
        // Handle the get cookie calls
        if (null === $key)
        {
            return $this->_cookies;
        }
        elseif ( ! is_array($key) && ! $value)
        {
            return Arr::get($this->_cookies, $key);
        }

        // Handle the set cookie calls
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                $this->cookie($k, $v);
            }
        }
        else
        {
            if ( ! is_array($value))
            {
                $value = [
                    'value'      => $value,
                    'expiration' => Cookie::$expiration
                ];
            }
            elseif ( ! isset($value['expiration']))
            {
                $value['expiration'] = Cookie::$expiration;
            }

            $this->_cookies[(string) $key] = $value;
        }

        return $this;
    }

    /**
     * 删除指定cookie
     *
     * @param  string $name
     * @return $this
     */
    public function deleteCookie($name)
    {
        unset($this->_cookies[$name]);
        return $this;
    }

    /**
     * 清空所有cookie
     *
     * @return $this
     */
    public function deleteCookies()
    {
        $this->_cookies = [];
        return $this;
    }

    /**
     * 发送当前保存的header信息
     *
     * @param  boolean  $replace  replace existing headers
     * @param  callback $callback function to handle header output
     * @return $this
     */
    public function sendHeaders($replace = false, $callback = null)
    {
        $protocol = $this->protocol;
        $status = $this->status;

        // Create the response header
        $renderHeaders = [$protocol . ' ' . $status . ' ' . Message::$text[$status]];

        // Get the headers array
        $headers = $this->headers();

        foreach ($headers as $header => $value)
        {
            if (is_array($value))
            {
                $value = implode(', ', $value);
            }

            // 格式化header的key
            $header = explode('-', $header);
            foreach ($header as $k => $v)
            {
                $header[$k] = ucfirst($v);
            }
            $header = implode('-', $header);

            $renderHeaders[] = $header . ': ' . $value;
        }

        if ( ! isset($headers['content-type']))
        {
            $renderHeaders[] = 'Content-Type: ' . Base::$contentType . '; charset=' . Base::$charset;
        }

        if (Base::$expose && ! isset($headers['x-powered-by']))
        {
            $renderHeaders[] = 'X-Powered-By: ' . Base::version();
        }

        if ($cookies = $this->cookie())
        {
            $renderHeaders['Set-Cookie'] = $cookies;
        }

        if (is_callable($callback))
        {
            // Use the callback method to set header
            return call_user_func($callback, $this, $renderHeaders, $replace);
        }
        else
        {
            $this->_sendHeadersToPhp($renderHeaders, $replace);

            return $this;
        }
    }

    /**
     * 发送header信息
     *
     * @param  array   $headers headers to send to php
     * @param  boolean $replace replace existing headers
     * @return $this
     */
    protected function _sendHeadersToPhp(array $headers, $replace)
    {
        if (Http::headersSent())
        {
            return $this;
        }

        foreach ($headers as $key => $line)
        {
            if ($key == 'Set-Cookie' && is_array($line))
            {
                // Send cookies
                foreach ($line as $name => $value)
                {
                    Cookie::set($name, $value['value'], $value['expiration']);
                }
                continue;
            }
            Http::header($line, $replace);
        }

        return $this;
    }

    /**
     * Send file download as the response. All execution will be halted when
     * this method is called! Use true for the filename to send the current
     * response as the file content. The third parameter allows the following
     * options to be set:
     * Type      | Option    | Description                        | Default Value
     * ----------|-----------|------------------------------------|--------------
     * `boolean` | inline    | Display inline instead of download | `false`
     * `string`  | mime_type | Manual mime type                   | Automatic
     * `boolean` | delete    | Delete the file after sending      | `false`
     *
     * Download a file that already exists:
     *
     *     $response->sendFile('media/packages/package.zip');
     *
     * Download generated content as a file:
     *
     *     $response->body = $content;
     *     $response->sendFile(true, $filename);
     *
     * [!!] No further processing can be done after this method is called!
     *
     * @param   string $filename filename with path, or true for the current response
     * @param   string $download downloaded file name
     * @param   array  $options  additional options
     * @return  void
     * @throws  BaseException
     */
    public function sendFile($filename, $download = null, array $options = null)
    {
        if ( ! empty($options['mime_type']))
        {
            // The mime-type has been manually set
            $mime = $options['mime_type'];
        }

        if (true === $filename)
        {
            if (empty($download))
            {
                throw new BaseException('Download name must be provided for streaming files');
            }

            // Temporary files will automatically be deleted
            $options['delete'] = false;

            if ( ! isset($mime))
            {
                // 根据扩展名，获取指定的mime类型
                $mime = Mime::getMimeFromExtension(strtolower(pathinfo($download, PATHINFO_EXTENSION)));
            }

            // Force the data to be rendered if
            $fileData = (string) $this->message->body;

            // Get the content size
            $size = strlen($fileData);

            // Create a temporary file to hold the current response
            $file = tmpfile();

            // Write the current response into the file
            fwrite($file, $fileData);

            // FileHelper data is no longer needed
            unset($fileData);
        }
        else
        {
            // Get the complete file path
            $filename = realpath($filename);

            if (empty($download))
            {
                // Use the file name as the download file name
                $download = pathinfo($filename, PATHINFO_BASENAME);
            }

            // Get the file size
            $size = filesize($filename);

            if ( ! isset($mime))
            {
                // 根据扩展名，获取指定的mime类型
                $mime = Mime::getMimeFromExtension(pathinfo($download, PATHINFO_EXTENSION));
            }

            // Open the file for reading
            $file = fopen($filename, 'rb');
        }

        if ( ! is_resource($file))
        {
            throw new BaseException('Could not read file to send: :file', [
                ':file' => $download,
            ]);
        }

        // Inline or download?
        $disposition = empty($options['inline']) ? 'attachment' : 'inline';

        // Calculate byte range to download.
        $temp = $this->_calculateByteRange($size);
        $start = array_shift($temp);
        $end = array_shift($temp);

        if ( ! empty($options['resumable']))
        {
            if ($start > 0 || $end < ($size - 1))
            {
                // Partial Content
                $this->status = 206;
            }

            // Range of bytes being sent
            $this->headers('content-range', 'bytes ' . $start . '-' . $end . '/' . $size);
            $this->headers('accept-ranges', 'bytes');
        }

        // Set the headers for a download
        $this->headers('content-disposition', $disposition . '; filename="' . $download . '"');
        $this->headers('content-type', $mime);
        $this->headers('content-length', (string) (($end - $start) + 1));

        // Send all headers now
        $this->sendHeaders();

        while (ob_get_level())
        {
            // Flush all output buffers
            ob_end_flush();
        }

        // Manually stop execution
        ignore_user_abort(true);

        if ( ! Base::$safeMode)
        {
            // Keep the script running forever
            @set_time_limit(0);
        }

        // Send data in 16kb blocks
        $block = 1024 * 16;

        fseek($file, $start);

        while ( ! feof($file) && ($pos = ftell($file)) <= $end)
        {
            if (connection_aborted())
            {
                break;
            }

            if ($pos + $block > $end)
            {
                // Don't read past the buffer.
                $block = $end - $pos + 1;
            }

            // Output a block of the file
            echo fread($file, $block);

            // Send the data now
            flush();
        }

        // Close the file
        fclose($file);

        if ( ! empty($options['delete']))
        {
            try
            {
                // Attempt to remove the file
                unlink($filename);
            }
            catch (Exception $e)
            {
                // Create a text version of the exception
                $error = BaseException::text($e);
                Log::error($error);
                // Do NOT display the exception, it will corrupt the output!
            }
        }

        // 停止执行
        Http::end();
    }

    /**
     * Renders the HTTP_Interaction to a string, producing
     *
     *  - Protocol
     *  - Headers
     *  - Body
     *
     * @return string
     */
    public function render()
    {
        if ( ! $this->headers('content-type'))
        {
            $this->headers('content-type', Base::$contentType . '; charset=' . Base::$charset);
        }

        // Set the content length
        $this->headers('content-length', (string) $this->contentLength);

        if (Base::$expose)
        {
            $this->headers('user-agent', Base::version());
        }

        // Prepare cookies
        if ($this->_cookies)
        {
            if (extension_loaded('http'))
            {
                $this->headers('set-cookie', http_build_cookie($this->_cookies));
            }
            else
            {
                $cookies = [];

                // Parse each
                foreach ($this->_cookies as $key => $value)
                {
                    $string = $key . '=' . $value['value'] . '; expires=' . date('l, d M Y H:i:s T', $value['expiration']);
                    $cookies[] = $string;
                }

                $this->headers('set-cookie', $cookies);
            }
        }

        $output = $this->protocol
            . ' '
            . $this->status
            . ' '
            . Message::$text[$this->status]
            . "\r\n";
        $output .= $this->message->headerLines;
        $output .= $this->message->body;

        return $output;
    }

    /**
     * Generate ETag
     * Generates an ETag from the response ready to be returned
     *
     * @throws RequestException
     * @return String Generated ETag
     */
    public function generateEtag()
    {
        if ('' === $this->body)
        {
            throw new RequestException('No response yet associated with request - cannot auto generate resource ETag');
        }

        // Generate a unique hash for the response
        return '"' . sha1($this->render()) . '"';
    }

    /**
     * 解析HTTP_RANGE
     *
     * @return array|false
     */
    protected function _parseByteRange()
    {
        if ( ! isset($_SERVER['HTTP_RANGE']))
        {
            return false;
        }

        // TODO, speed this up with the use of string functions.
        preg_match_all('/(-?[0-9]++(?:-(?![0-9]++))?)(?:-?([0-9]++))?/', $_SERVER['HTTP_RANGE'], $matches, PREG_SET_ORDER);

        return $matches[0];
    }

    /**
     * Calculates the byte range to use with send_file. If HTTP_RANGE does not exist then the complete byte range is returned
     *
     * @param  int $size
     * @return array
     */
    protected function _calculateByteRange($size)
    {
        // Defaults to start with when the HTTP_RANGE header doesn't exist.
        $start = 0;
        $end = $size - 1;

        if ($range = $this->_parseByteRange())
        {
            // We have a byte range from HTTP_RANGE
            $start = $range[1];

            if ('-' === $start[0])
            {
                // A negative value means we start from the end, so -500 would be the
                // last 500 bytes.
                $start = $size - abs($start);
            }

            if (isset($range[2]))
            {
                // Set the end range
                $end = $range[2];
            }
        }

        // Normalize values.
        $start = abs(intval($start));

        // Keep the the end value in bounds and normalize it.
        $end = min(abs(intval($end)), $size - 1);

        // Keep the start in bounds.
        $start = ($end < $start) ? 0 : max($start, 0);

        return [
            $start,
            $end
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->message->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $this->message->withProtocolVersion($version);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->message->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->message->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return $this->message->getHeaderLine($name);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $this->message->withHeader($name, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $this->message->withAddedHeader($name, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $this->message->withoutHeader($name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $this->message->withBody($body);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->status = $code;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return isset(Message::$text[$this->status]) ? Message::$text[$this->status] : '';
    }
}

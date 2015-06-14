<?php

namespace tourze\Http;

use Exception;
use Hoa\Mime\Mime;
use tourze\Base\Log;
use tourze\Base\Object;
use tourze\Base\Exception\BaseException;
use tourze\Base\Helper\Arr;
use tourze\Base\Helper\Cookie;
use tourze\Base\Base;
use tourze\Http\Request\Exception\RequestException;

/**
 * Response wrapper. Created as the result of any [Request] execution
 * or utility method (i.e. Redirect). Implements standard HTTP
 * response format.
 *
 * @property   string     protocol
 * @property   string     body
 * @property   integer    contentLength
 * @property   integer    status
 * @property   HttpHeader header
 */
class HttpResponse extends Object
{

    /**
     * Factory method to create a new [Response]. Pass properties
     * in using an associative array.
     *      // Create a new response
     *      $response = Response::factory();
     *      // Create a new response with headers
     *      $response = Response::factory(['status' => 200]);
     *
     * @param   array $config Setup the response object
     *
     * @return  $this
     */
    public static function factory(array $config = [])
    {
        return new HttpResponse($config);
    }

    // HTTP status codes and messages
    public static $messages = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];

    /**
     * @var  integer     The response http status
     */
    protected $_status = 200;

    /**
     * @return HttpHeader
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * @param HttpHeader $header
     */
    public function setHeader($header)
    {
        $this->_header = $header;
    }

    protected function getStatus()
    {
        return $this->_status;
    }

    protected function setStatus($status)
    {
        if (array_key_exists($status, self::$messages))
        {
            $this->_status = (int) $status;
        }
        else
        {
            throw new BaseException(__METHOD__ . ' unknown status value : :value', [':value' => $status]);
        }
    }

    /**
     * @var  HttpHeader  Headers returned in the response
     */
    protected $_header;

    /**
     * @var  string      相应的内容主体
     */
    protected $_body = '';

    protected function getBody()
    {
        return $this->_body;
    }

    protected function setBody($content)
    {
        $this->_body = (string) $content;
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
     * Sets up the response object
     *
     * @param   array $config Setup the response object
     */
    public function __construct(array $config = [])
    {
        $this->header = new HttpHeader();

        foreach ($config as $key => $value)
        {
            if (property_exists($this, $key))
            {
                if ('_header' == $key)
                {
                    $this->headers($value);
                }
                else
                {
                    $this->$key = $value;
                }
            }
        }
    }

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
     * @param mixed  $key
     * @param string $value
     *
     * @return mixed
     */
    public function headers($key = null, $value = null)
    {
        if (null === $key)
        {
            return $this->_header;
        }
        elseif (is_array($key))
        {
            $this->_header->exchangeArray($key);

            return $this;
        }
        elseif (null === $value)
        {
            return Arr::get((array) $this->_header, $key);
        }
        else
        {
            $this->_header[$key] = $value;

            return $this;
        }
    }

    /**
     * Returns the length of the body for use with content header
     *
     * @return  integer
     */
    public function getContentLength()
    {
        return strlen($this->body);
    }

    /**
     * Set and get cookies values for this response.
     *     // Get the cookies set to the response
     *     $cookies = $response->cookie();
     *     // Set a cookie to the response
     *     $response->cookie('session', [
     *          'value' => $value,
     *          'expiration' => 12352234
     *     ]);
     *
     * @param   mixed  $key   cookie name, or array of cookie values
     * @param   string $value value to set to cookie
     *
     * @return  string
     * @return  void
     * @return  [Response]
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
            reset($key);
            while (list($_key, $_value) = each($key))
            {
                $this->cookie($_key, $_value);
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
     * Deletes a cookie set to the response
     *
     * @param   string $name
     *
     * @return  $this
     */
    public function deleteCookie($name)
    {
        unset($this->_cookies[$name]);

        return $this;
    }

    /**
     * Deletes all cookies from this response
     *
     * @return  $this
     */
    public function deleteCookies()
    {
        $this->_cookies = [];

        return $this;
    }

    /**
     * Sends the response status and all set headers.
     *
     * @param   boolean  $replace  replace existing headers
     * @param   callback $callback function to handle header output
     *
     * @return  mixed
     */
    public function sendHeaders($replace = false, $callback = null)
    {
        return $this->_header->sendHeaders($this, $replace, $callback);
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
     * Download a file that already exists:
     *     $request->sendFile('media/packages/package.zip');
     * Download generated content as a file:
     *     $request->response($content);
     *     $request->sendFile(true, $filename);
     * [!!] No further processing can be done after this method is called!
     *
     * @param   string $filename filename with path, or true for the current response
     * @param   string $download downloaded file name
     * @param   array  $options  additional options
     *
     * @return  void
     * @throws  BaseException
     * @uses    \tourze\Base\Helper\File::mime_by_ext
     * @uses    \tourze\Base\Helper\File::mime
     * @uses    Request::sendHeaders
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
            $fileData = (string) $this->_body;

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
        list($start, $end) = $this->_calculateByteRange($size);

        if ( ! empty($options['resumable']))
        {
            if ($start > 0 || $end < ($size - 1))
            {
                // Partial Content
                $this->status = 206;
            }

            // Range of bytes being sent
            $this->_header['content-range'] = 'bytes ' . $start . '-' . $end . '/' . $size;
            $this->_header['accept-ranges'] = 'bytes';
        }

        // Set the headers for a download
        $this->_header['content-disposition'] = $disposition . '; filename="' . $download . '"';
        $this->_header['content-type'] = $mime;
        $this->_header['content-length'] = (string) (($end - $start) + 1);

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

        // Stop execution
        exit;
    }

    /**
     * Renders the HTTP_Interaction to a string, producing
     *  - Protocol
     *  - Headers
     *  - Body
     *
     * @return  string
     */
    public function render()
    {
        if ( ! $this->_header->offsetExists('content-type'))
        {
            // Add the default Content-Type header if required
            $this->_header['content-type'] = Base::$contentType . '; charset=' . Base::$charset;
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
                $this->_header['set-cookie'] = http_build_cookie($this->_cookies);
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

                // Create the cookie string
                $this->_header['set-cookie'] = $cookies;
            }
        }

        $output = $this->protocol
            . ' '
            . $this->status
            . ' '
            . self::$messages[$this->status]
            . "\r\n";
        $output .= (string) $this->_header;
        $output .= $this->_body;

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
     * @param  integer $size
     *
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

}

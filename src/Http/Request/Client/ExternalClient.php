<?php

namespace tourze\Http\Request\Client;

use Exception;
use tourze\Base\Helper\Arr;
use tourze\Base\Base;
use tourze\Http\Http;
use tourze\Http\Response;
use tourze\Http\Request;
use tourze\Http\Request\Exception\RequestException;
use tourze\Http\Request\RequestClient;

/**
 * 获取指定地址的外部请求
 *
 * @package tourze\Http\Request\Client
 */
class ExternalClient extends RequestClient
{

    /**
     * @var     array     curl options
     * @link    http://www.php.net/manual/function.curl-setopt
     * @link    http://www.php.net/manual/http.request.options
     */
    protected $_options = [];

    /**
     * Processes the request, executing the controller action that handles this
     * request, determined by the [Route].
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     * By default, the output from the controller is captured and returned, and
     * no headers are sent.
     *     $request->execute();
     *
     * @param  Request  $request  A request object
     * @param  Response $response A response object
     * @return Response
     * @throws Exception
     */
    public function executeRequest(Request $request, Response $response)
    {
        $previous = Request::$current;
        Request::$current = $request;

        // 如果post数据了
        if ($post = $request->post())
        {
            $request->body = http_build_query($post, null, '&');
            $request->headers('content-type', 'application/x-www-form-urlencoded; charset=' . Base::$charset);
        }
        if (Base::$expose)
        {
            $request->headers('user-agent', Base::version());
        }
        try
        {
            $response = $this->_sendMessage($request, $response);
        }
        catch (Exception $e)
        {
            // Restore the previous request
            Request::$current = $previous;
            throw $e;
        }

        // Restore the previous request
        Request::$current = $previous;

        // Return the response
        return $response;
    }

    /**
     * Set and get options for this request.
     *
     * @param   mixed $key   Option name, or array of options
     * @param   mixed $value Option value
     *
     * @return  mixed
     * @return  $this
     */
    public function options($key = null, $value = null)
    {
        if (null === $key)
        {
            return $this->_options;
        }

        if (is_array($key))
        {
            $this->_options = $key;
        }
        elseif (null === $value)
        {
            return Arr::get($this->_options, $key);
        }
        else
        {
            $this->_options[$key] = $value;
        }

        return $this;
    }

    /**
     * 发送HTTP请求，并处理返回数据
     *
     * @param   Request  $request response to send
     * @param   Response $response
     * @return  Response
     * @throws  RequestException
     */
    public function _sendMessage(Request $request, Response $response)
    {
        $options = [];
        // Set the request method
        $options = $this->_setCurlRequestMethod($request, $options);

        // Set the request body. This is perfectly legal in CURL even
        // if using a request other than POST. PUT does support this method
        // and DOES NOT require writing data to disk before putting it, if
        // reading the PHP docs you may have got that impression. SdF
        $options[CURLOPT_POSTFIELDS] = $request->body;

        // Process headers
        if ($headers = $request->headers())
        {
            $httpHeaders = [];
            foreach ($headers as $key => $value)
            {
                if (is_array($value))
                {
                    $value = implode(', ', $value);
                }
                $httpHeaders[] = $key . ': ' . $value;
            }
            $options[CURLOPT_HTTPHEADER] = $httpHeaders;
        }

        // Process cookies
        if ($cookies = $request->cookie())
        {
            $options[CURLOPT_COOKIE] = http_build_query($cookies, null, '; ');
        }

        $this->_options[CURLOPT_RETURNTRANSFER] = true;
        $this->_options[CURLOPT_HEADER] = false;

        // Apply any additional options set to
        $options += $this->_options;
        $uri = $request->uri;
        if ($query = $request->query())
        {
            $uri .= '?' . http_build_query($query, null, '&');
        }

        // Open a new remote connection
        $curl = curl_init($uri);
        // Set connection options
        if ( ! curl_setopt_array($curl, $options))
        {
            throw new RequestException('Failed to set CURL options, check CURL documentation: :url', [
                ':url' => 'http://php.net/curl_setopt_array'
            ]);
        }

        // Get the response body
        $body = curl_exec($curl);
        // Get the response information
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (false === $body)
        {
            $error = curl_error($curl);
        }
        // Close the connection
        curl_close($curl);

        if (isset($error))
        {
            throw new RequestException('Error fetching remote :url [ status :code ] :error', [
                ':url'   => $request->url(),
                ':code'  => $code,
                ':error' => $error
            ]);
        }
        $response->status = $code;
        $response->body = $body;

        return $response;
    }

    /**
     * 设置CURL请求选项
     *
     * @param Request $request
     * @param array   $options
     * @return array
     */
    public function _setCurlRequestMethod(Request $request, array $options)
    {
        switch ($request->method)
        {
            case Http::POST:
                $options[CURLOPT_POST] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $request->method;
                break;
        }

        return $options;
    }

}

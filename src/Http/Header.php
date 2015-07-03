<?php

namespace tourze\Http;

use ArrayObject;
use tourze\Base\Helper\Cookie;
use tourze\Base\Base;

/**
 * The Header class provides an Object-Orientated interface
 * to HTTP headers. This can parse header arrays returned from the
 * PHP functions `apache_request_headers()` or the `http_parse_headers()`
 * function available within the PECL HTTP library.
 *
 * @package    Base
 * @category   HTTP
 * @author     YwiSax
 */
class Header extends ArrayObject
{

    // Default Accept-* quality value if none supplied
    const DEFAULT_QUALITY = 1;

    /**
     * Parses an Accept(-*) header and detects the quality
     *
     * @param   array $parts accept header parts
     *
     * @return  array
     */
    public static function accept_quality(array $parts)
    {
        $parsed = [];

        // Resource light iteration
        $partsKeys = array_keys($parts);
        foreach ($partsKeys as $key)
        {
            $value = trim(str_replace([
                "\r",
                "\n"
            ], '', $parts[$key]));

            $pattern = '~\b(\;\s*+)?q\s*+=\s*+([.0-9]+)~';

            // If there is no quality directive, return default
            if ( ! preg_match($pattern, $value, $quality))
            {
                $parsed[$value] = (float) self::DEFAULT_QUALITY;
            }
            else
            {
                $quality = $quality[2];

                if ($quality[0] === '.')
                {
                    $quality = '0' . $quality;
                }

                // Remove the quality value from the string and apply quality
                $parsed[trim(preg_replace($pattern, '', $value, 1), '; ')] = (float) $quality;
            }
        }

        return $parsed;
    }

    /**
     * Parses the accept header to provide the correct quality values
     * for each supplied accept type.
     *
     * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
     *
     * @param   string $accepts accept content header string to parse
     *
     * @return  array
     */
    public static function parseAcceptHeader($accepts = null)
    {
        $accepts = explode(',', (string) $accepts);

        // If there is no accept, lets accept everything
        if (null === $accepts)
        {
            return ['*' => ['*' => (float) self::DEFAULT_QUALITY]];
        }

        // Parse the accept header qualities
        $accepts = self::accept_quality($accepts);

        $parsedAccept = [];

        // This method of iteration uses less resource
        $keys = array_keys($accepts);
        foreach ($keys as $key)
        {
            // Extract the parts
            $parts = explode('/', $key, 2);

            // Invalid content type- bail
            if ( ! isset($parts[1]))
            {
                continue;
            }

            // Set the parsed output
            $parsedAccept[$parts[0]][$parts[1]] = $accepts[$key];
        }

        return $parsedAccept;
    }

    /**
     * Parses the `Accept-Charset:` HTTP header and returns an array containing
     * the charset and associated quality.
     *
     * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.2
     *
     * @param   string $charset charset string to parse
     *
     * @return  array
     */
    public static function parseCharsetHeader($charset = null)
    {
        if (null === $charset)
        {
            return ['*' => (float) self::DEFAULT_QUALITY];
        }

        return self::accept_quality(explode(',', (string) $charset));
    }

    /**
     * Parses the `Accept-Encoding:` HTTP header and returns an array containing
     * the charsets and associated quality.
     *
     * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.3
     *
     * @param   string $encoding charset string to parse
     *
     * @return  array
     */
    public static function parseEncodingHeader($encoding = null)
    {
        // Accept everything
        if (null === $encoding)
        {
            return ['*' => (float) self::DEFAULT_QUALITY];
        }
        elseif ($encoding === '')
        {
            return ['identity' => (float) self::DEFAULT_QUALITY];
        }
        else
        {
            return self::accept_quality(explode(',', (string) $encoding));
        }
    }

    /**
     * Parses the `Accept-Language:` HTTP header and returns an array containing
     * the languages and associated quality.
     *
     * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
     *
     * @param   string $language charset string to parse
     *
     * @return  array
     */
    public static function parseLanguageHeader($language = null)
    {
        if (null === $language)
        {
            return ['*' => ['*' => (float) self::DEFAULT_QUALITY]];
        }

        $language = self::accept_quality(explode(',', (string) $language));

        $parsedLanguage = [];

        $keys = array_keys($language);
        foreach ($keys as $key)
        {
            // Extract the parts
            $parts = explode('-', $key, 2);

            // Invalid content type- bail
            if ( ! isset($parts[1]))
            {
                $parsedLanguage[$parts[0]]['*'] = $language[$key];
            }
            else
            {
                // Set the parsed output
                $parsedLanguage[$parts[0]][$parts[1]] = $language[$key];
            }
        }

        return $parsedLanguage;
    }

    /**
     * Generates a Cache-Control HTTP header based on the supplied array.
     *
     *     // Set the cache control headers you want to use
     *     $cacheControl = [
     *         'max-age'          => 3600,
     *         'must-revalidate',
     *         'public'
     *     ];
     *     // Create the cache control header, creates :
     *     // cache-control: max-age=3600, must-revalidate, public
     *     $response->headers('Cache-Control', Header::createCacheControl($cacheControl);
     *
     * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13
     *
     * @param   array $cacheControl Cache-Control to render to string
     *
     * @return  string
     */
    public static function createCacheControl(array $cacheControl)
    {
        $parts = [];

        foreach ($cacheControl as $key => $value)
        {
            $parts[] = (is_int($key)) ? $value : ($key . '=' . $value);
        }

        return implode(', ', $parts);
    }

    /**
     * Parses the Cache-Control header and returning an array representation of the Cache-Control header.
     *
     *     // Create the cache control header
     *     $response->headers('cache-control', 'max-age=3600, must-revalidate, public');
     *     // Parse the cache control header
     *     if ($cacheControl = Header::parseCacheControl($response->headers('cache-control')))
     *     {
     *          // Cache-Control header was found
     *          $maxAge = $cacheControl['max-age'];
     *     }
     *
     * @param   array $cacheControl Array of headers
     *
     * @return  mixed
     */
    public static function parseCacheControl($cacheControl)
    {
        $directives = explode(',', strtolower($cacheControl));

        if (false === $directives)
        {
            return false;
        }

        $output = [];

        foreach ($directives as $directive)
        {
            if (false !== strpos($directive, '='))
            {
                list($key, $value) = explode('=', trim($directive), 2);

                $output[$key] = ctype_digit($value) ? (int) $value : $value;
            }
            else
            {
                $output[] = trim($directive);
            }
        }

        return $output;
    }

    /**
     * @var     array    Accept: (content) types
     */
    protected $_acceptContent;

    /**
     * @var     array    Accept-Charset: parsed header
     */
    protected $_acceptCharset;

    /**
     * @var     array    Accept-Encoding: parsed header
     */
    protected $_acceptEncoding;

    /**
     * @var     array    Accept-Language: parsed header
     */
    protected $_acceptLanguage;

    /**
     * Constructor method for [Header]. Uses the standard constructor of the parent `ArrayObject` class.
     *
     *     $header_object = new Header(['x-powered-by' => 'PHP 4.1.9', 'expires' => '...']);
     *
     * @param   mixed  $input          Input array
     * @param   int    $flags          Flags
     * @param   string $iterator_class The iterator class to use
     */
    public function __construct(array $input = [], $flags = 0, $iterator_class = 'ArrayIterator')
    {
        /**
         * @link http://www.w3.org/Protocols/rfc2616/rfc2616.html
         * HTTP header declarations should be treated as case-insensitive
         */
        $input = array_change_key_case((array) $input, CASE_LOWER);

        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     * Returns the header object as a string, including the terminating new line
     *
     *     // Return the header as a string
     *     echo (string) $request->headers();
     *
     * @return  string
     */
    public function __toString()
    {
        $header = '';

        foreach ($this as $key => $value)
        {
            // 格式化header的key
            $key = explode('-', $key);
            foreach ($key as $k => $v)
            {
                $key[$k] = ucfirst($v);
            }
            $key = implode('-', $key);

            if (is_array($value))
            {
                $header .= $key . ': ' . (implode(', ', $value)) . "\r\n";
            }
            else
            {
                $header .= $key . ': ' . $value . "\r\n";
            }
        }

        return $header . "\r\n";
    }

    /**
     * Overloads `ArrayObject::offsetSet()` to enable handling of header
     * with multiple instances of the same directive. If the `$replace` flag
     * is `false`, the header will be appended rather than replacing the
     * original setting.
     *
     * @param   mixed   $index   index to set `$newVal` to
     * @param   mixed   $newVal  new value to set
     * @param   boolean $replace replace existing value
     *
     * @return  void
     */
    public function offsetSet($index, $newVal, $replace = true)
    {
        // Ensure the index is lowercase
        $index = strtolower($index);

        if ($replace || ! $this->offsetExists($index))
        {
            parent::offsetSet($index, $newVal);

            return;
        }

        $currentValue = $this->offsetGet($index);

        if (is_array($currentValue))
        {
            $currentValue[] = $newVal;
        }
        else
        {
            $currentValue = [
                $currentValue,
                $newVal
            ];
        }

        parent::offsetSet($index, $currentValue);
    }

    /**
     * Overloads the `ArrayObject::offsetExists()` method to ensure keys
     * are lowercase.
     *
     * @param   string $index
     * @return  boolean
     */
    public function offsetExists($index)
    {
        return parent::offsetExists(strtolower($index));
    }

    /**
     * Overloads the `ArrayObject::offsetUnset()` method to ensure keys
     * are lowercase.
     *
     * @param   string $index
     * @return  void
     */
    public function offsetUnset($index)
    {
        parent::offsetUnset(strtolower($index));
    }

    /**
     * Overload the `ArrayObject::offsetGet()` method to ensure that all
     * keys passed to it are formatted correctly for this object.
     *
     * @param   string $index index to retrieve
     *
     * @return  mixed
     */
    public function offsetGet($index)
    {
        return parent::offsetGet(strtolower($index));
    }

    /**
     * Overloads the `ArrayObject::exchangeArray()` method to ensure that
     * all keys are changed to lowercase.
     *
     * @param   mixed $input
     * @return  array
     */
    public function exchangeArray($input)
    {
        /**
         * @link http://www.w3.org/Protocols/rfc2616/rfc2616.html
         * HTTP header declarations should be treated as case-insensitive
         */
        $input = array_change_key_case((array) $input, CASE_LOWER);

        return parent::exchangeArray($input);
    }

    /**
     * Parses a HTTP Message header line and applies it to this Header
     *
     *     $header = $response->headers();
     *     $header->parseHeaderString(null, 'content-type: application/json');
     *
     * @param   resource $resource   the resource (required by Curl API)
     * @param   string   $headerLine the line from the header to parse
     * @return  int
     */
    public function parseHeaderString($resource, $headerLine)
    {
        if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $headerLine, $matches))
        {
            foreach ($matches[0] as $key => $value)
            {
                $this->offsetSet($matches[1][$key], $matches[2][$key], false);
            }
        }

        return strlen($headerLine);
    }

    /**
     * Returns the accept quality of a submitted mime type based on the
     * request `Accept:` header. If the `$explicit` argument is `true`,
     * only precise matches will be returned, excluding all wildcard (`*`)
     * directives.
     *
     *     // Accept: application/xml; application/json; q=.5; text/html; q=.2, text/*
     *     // Accept quality for application/json
     *     // $quality = 0.5
     *     $quality = $request->headers()->acceptsAtQuality('application/json');
     *     // $quality_explicit = false
     *     $quality_explicit = $request->headers()->acceptsAtQuality('text/plain', true);
     *
     * @param   string  $type
     * @param   boolean $explicit explicit check, excludes `*`
     * @return  mixed
     */
    public function acceptsAtQuality($type, $explicit = false)
    {
        // Parse Accept header if required
        if (null === $this->_acceptContent)
        {
            if ($this->offsetExists('Accept'))
            {
                $accept = $this->offsetGet('Accept');
            }
            else
            {
                $accept = '*/*';
            }

            $this->_acceptContent = self::parseAcceptHeader($accept);
        }

        $parts = explode('/', $type, 2);

        if (isset($this->_acceptContent[$parts[0]][$parts[1]]))
        {
            return $this->_acceptContent[$parts[0]][$parts[1]];
        }
        elseif (true === $explicit)
        {
            return false;
        }
        else
        {
            if (isset($this->_acceptContent[$parts[0]]['*']))
            {
                return $this->_acceptContent[$parts[0]]['*'];
            }
            elseif (isset($this->_acceptContent['*']['*']))
            {
                return $this->_acceptContent['*']['*'];
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * Returns the preferred response content type based on the accept header
     * quality settings. If items have the same quality value, the first item
     * found in the array supplied as `$types` will be returned.
     *
     *     // Get the preferred acceptable content type
     *     // Accept: text/html, application/json; q=.8, text/*
     *     $result = $header->preferredAccept([
     *         'text/html'
     *         'text/rtf',
     *         'application/json'
     *     ]); // $result = 'application/json'
     *     $result = $header->preferredAccept([
     *         'text/rtf',
     *         'application/xml'
     *     ], true); // $result = false (none matched explicitly)
     *
     * @param   array   $types    the content types to examine
     * @param   boolean $explicit only allow explicit references, no wildcards
     *
     * @return  string  name of the preferred content type
     */
    public function preferredAccept(array $types, $explicit = false)
    {
        $preferred = false;
        $ceiling = 0;

        foreach ($types as $type)
        {
            $quality = $this->acceptsAtQuality($type, $explicit);

            if ($quality > $ceiling)
            {
                $preferred = $type;
                $ceiling = $quality;
            }
        }

        return $preferred;
    }

    /**
     * Returns the quality of the supplied `$charset` argument. This method
     * will automatically parse the `Accept-Charset` header if present and
     * return the associated resolved quality value.
     *
     *      // Accept-Charset: utf-8, utf-16; q=.8, iso-8859-1; q=.5
     *      $quality = $header->acceptsCharsetAtQuality('utf-8');
     *      // $quality = (float) 1
     *
     * @param   string $charset charset to examine
     *
     * @return  float   the quality of the charset
     */
    public function acceptsCharsetAtQuality($charset)
    {
        if (null === $this->_acceptCharset)
        {
            if ($this->offsetExists('Accept-Charset'))
            {
                $charsetHeader = strtolower($this->offsetGet('Accept-Charset'));
                $this->_acceptCharset = self::parseCharsetHeader($charsetHeader);
            }
            else
            {
                $this->_acceptCharset = self::parseCharsetHeader(null);
            }
        }

        $charset = strtolower($charset);

        if (isset($this->_acceptCharset[$charset]))
        {
            return $this->_acceptCharset[$charset];
        }
        elseif (isset($this->_acceptCharset['*']))
        {
            return $this->_acceptCharset['*'];
        }
        elseif ($charset === 'iso-8859-1')
        {
            return (float) 1;
        }

        return (float) 0;
    }

    /**
     * Returns the preferred charset from the supplied array `$charsets` based
     * on the `Accept-Charset` header directive.
     *
     *      // Accept-Charset: utf-8, utf-16; q=.8, iso-8859-1; q=.5
     *      $charset = $header->preferredCharset([
     *          'utf-10', 'ascii', 'utf-16', 'utf-8'
     *      ]); // $charset = 'utf-8'
     *
     * @param   array $charsets charsets to test
     *
     * @return  mixed   preferred charset or `false`
     */
    public function preferredCharset(array $charsets)
    {
        $preferred = false;
        $ceiling = 0;

        foreach ($charsets as $charset)
        {
            $quality = $this->acceptsCharsetAtQuality($charset);

            if ($quality > $ceiling)
            {
                $preferred = $charset;
                $ceiling = $quality;
            }
        }

        return $preferred;
    }

    /**
     * Returns the quality of the `$encoding` type passed to it. Encoding
     * is usually compression such as `gzip`, but could be some other
     * message encoding algorithm. This method allows explicit checks to be
     * done ignoring wildcards.
     *
     *      // Accept-Encoding: compress, gzip, *; q=.5
     *      $encoding = $header->accepts_encoding_at_quality('gzip');
     *      // $encoding = (float) 1.0s
     *
     * @param   string  $encoding encoding type to interrogate
     * @param   boolean $explicit explicit check, ignoring wildcards and `identity`
     *
     * @return  float
     */
    public function acceptsEncodingAtQuality($encoding, $explicit = false)
    {
        if (null === $this->_acceptEncoding)
        {
            if ($this->offsetExists('Accept-Encoding'))
            {
                $encodingHeader = $this->offsetGet('Accept-Encoding');
            }
            else
            {
                $encodingHeader = null;
            }

            $this->_acceptEncoding = self::parseEncodingHeader($encodingHeader);
        }

        // Normalize the encoding
        $encoding = strtolower($encoding);

        if (isset($this->_acceptEncoding[$encoding]))
        {
            return $this->_acceptEncoding[$encoding];
        }

        if (false === $explicit)
        {
            if (isset($this->_acceptEncoding['*']))
            {
                return $this->_acceptEncoding['*'];
            }
            elseif ($encoding === 'identity')
            {
                return (float) self::DEFAULT_QUALITY;
            }
        }

        return (float) 0;
    }

    /**
     * Returns the preferred message encoding type based on quality, and can
     * optionally ignore wildcard references. If two or more encodings have the
     * same quality, the first listed in `$encodings` will be returned.
     *
     *     // Accept-Encoding: compress, gzip, *; q.5
     *     $encoding = $header->preferredEncoding([
     *          'gzip', 'bzip', 'blowfish'
     *     ]);
     *     // $encoding = 'gzip';
     *
     * @param   array   $encodings encodings to test against
     * @param   boolean $explicit  explicit check, if `true` wildcards are excluded
     *
     * @return  mixed
     */
    public function preferredEncoding(array $encodings, $explicit = false)
    {
        $ceiling = 0;
        $preferred = false;

        foreach ($encodings as $encoding)
        {
            $quality = $this->acceptsEncodingAtQuality($encoding, $explicit);

            if ($quality > $ceiling)
            {
                $ceiling = $quality;
                $preferred = $encoding;
            }
        }

        return $preferred;
    }

    /**
     * Returns the quality of `$language` supplied, optionally ignoring
     * wildcards if `$explicit` is set to a non-`false` value. If the quality
     * is not found, `0.0` is returned.
     *
     *     // Accept-Language: en-us, en-gb; q=.7, en; q=.5
     *     $lang = $header->acceptsLanguageAtQuality('en-gb');
     *     // $lang = (float) 0.7
     *     $lang2 = $header->acceptsLanguageAtQuality('en-au');
     *     // $lang2 = (float) 0.5
     *     $lang3 = $header->acceptsLanguageAtQuality('en-au', true);
     *     // $lang3 = (float) 0.0
     *
     * @param   string  $language language to interrogate
     * @param   boolean $explicit explicit interrogation, `true` ignores wildcards
     *
     * @return  float
     */
    public function acceptsLanguageAtQuality($language, $explicit = false)
    {
        if (null === $this->_acceptLanguage)
        {
            if ($this->offsetExists('Accept-Language'))
            {
                $languageHeader = strtolower($this->offsetGet('Accept-Language'));
            }
            else
            {
                $languageHeader = null;
            }

            $this->_acceptLanguage = self::parseLanguageHeader($languageHeader);
        }

        // Normalize the language
        $languageParts = explode('-', strtolower($language), 2);

        if (isset($this->_acceptLanguage[$languageParts[0]]))
        {
            if (isset($languageParts[1]))
            {
                if (isset($this->_acceptLanguage[$languageParts[0]][$languageParts[1]]))
                {
                    return $this->_acceptLanguage[$languageParts[0]][$languageParts[1]];
                }
                elseif (false === $explicit && isset($this->_acceptLanguage[$languageParts[0]]['*']))
                {
                    return $this->_acceptLanguage[$languageParts[0]]['*'];
                }
            }
            elseif (isset($this->_acceptLanguage[$languageParts[0]]['*']))
            {
                return $this->_acceptLanguage[$languageParts[0]]['*'];
            }
        }

        if (false === $explicit && isset($this->_acceptLanguage['*']))
        {
            return $this->_acceptLanguage['*'];
        }

        return (float) 0;
    }

    /**
     * Returns the preferred language from the supplied array `$languages` based
     * on the `Accept-Language` header directive.
     *      // Accept-Language: en-us, en-gb; q=.7, en; q=.5
     *      $lang = $header->preferredLanguage([
     *          'en-gb', 'en-au', 'fr', 'es'
     *      ]); // $lang = 'en-gb'
     *
     * @param   array   $languages
     * @param   boolean $explicit
     *
     * @return  mixed
     */
    public function preferredLanguage(array $languages, $explicit = false)
    {
        $ceiling = 0;
        $preferred = false;

        foreach ($languages as $language)
        {
            $quality = $this->acceptsLanguageAtQuality($language, $explicit);

            if ($quality > $ceiling)
            {
                $ceiling = $quality;
                $preferred = $language;
            }
        }

        return $preferred;
    }

    /**
     * Sends headers to the php processor, or supplied `$callback` argument.
     * This method formats the headers correctly for output, re-instating their
     * capitalization for transmission.
     * [!!] if you supply a custom header handler via `$callback`, it is
     *  recommended that `$response` is returned
     *
     * @param   Response  $response header to send
     * @param   boolean      $replace  replace existing value
     * @param   callback     $callback optional callback to replace PHP header function
     *
     * @return  mixed
     */
    public function sendHeaders(Response $response = null, $replace = false, $callback = null)
    {
        $protocol = $response->protocol;
        $status = $response->status;

        // Create the response header
        $processedHeaders = [$protocol . ' ' . $status . ' ' . Response::$messages[$status]];

        // Get the headers array
        $headers = $response->headers()
            ->getArrayCopy();

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

            $processedHeaders[] = $header . ': ' . $value;
        }

        if ( ! isset($headers['content-type']))
        {
            $processedHeaders[] = 'Content-Type: ' . Base::$contentType . '; charset=' . Base::$charset;
        }

        if (Base::$expose && ! isset($headers['x-powered-by']))
        {
            $processedHeaders[] = 'X-Powered-By: ' . Base::version();
        }

        // Get the cookies and apply
        if ($cookies = $response->cookie())
        {
            $processedHeaders['Set-CookieHelper'] = $cookies;
        }

        if (is_callable($callback))
        {
            // Use the callback method to set header
            return call_user_func($callback, $response, $processedHeaders, $replace);
        }
        else
        {
            $this->_sendHeadersToPhp($processedHeaders, $replace);

            return $response;
        }
    }

    /**
     * Sends the supplied headers to the PHP output buffer. If cookies
     * are included in the message they will be handled appropriately.
     *
     * @param   array   $headers headers to send to php
     * @param   boolean $replace replace existing headers
     *
     * @return  Header
     */
    protected function _sendHeadersToPhp(array $headers, $replace)
    {
        // If the headers have been sent, get out
        if (headers_sent())
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
            header($line, $replace);
        }

        return $this;
    }

}

<?php

namespace tourze\Http;

/**
 * The HTTP Interaction interface providing the core HTTP methods that
 * should be implemented by any HTTP request or response class.
 *
 * @package    Base
 * @category   HTTP
 * @author     YwiSax
 */
interface Message
{

    /**
     * @var int
     */
    const CONTINUES = 100;

    /**
     * @var int
     */
    const SWITCHING_PROTOCOLS = 101;

    //.........................................................................
    //. Success/Status (2xx)
    //.........................................................................

    /**
     * @var int
     */
    const OK = 200;
    /**
     * @var int
     */
    const CREATED = 201;
    /**
     * @var int
     */
    const ACCEPTED = 202;
    /**
     * @var int
     */
    const NON_AUTHORITATIVE_INFORMATION = 203;
    /**
     * @var int
     */
    const NO_CONTENT = 204;
    /**
     * @var int
     */
    const RESET_CONTENT = 205;
    /**
     * @var int
     */
    const PARTIAL_CONTENT = 206;

    //.........................................................................
    //. Redirection (3xx)
    //.........................................................................

    /**
     * @var int
     */
    const MULTIPLE_CHOICES = 300;
    /**
     * @var int
     */
    const MOVED_PERMANENTLY = 301;
    /**
     * @var int
     */
    const FOUND = 302;
    /**
     * @var int
     */
    const SEE_OTHER = 303;
    /**
     * @var int
     */
    const NOT_MODIFIED = 304;
    /**
     * @var int
     */
    const USE_PROXY = 305;
    /**
     * @var int
     */
    const TEMPORARY_REDIRECT = 307;

    //.........................................................................
    //. Client Errors (4xx)
    //.........................................................................

    /**
     * @var int
     */
    const BAD_REQUEST = 400;
    /**
     * @var int
     */
    const UNAUTHORIZED = 401;
    /**
     * @var int
     */
    const PAYMENT_REQUIRED = 402;
    /**
     * @var int
     */
    const FORBIDDEN = 403;
    /**
     * @var int
     */
    const NOT_FOUND = 404;
    /**
     * @var int
     */
    const METHOD_NOT_ALLOWED = 405;
    /**
     * @var int
     */
    const NOT_ACCEPTABLE = 406;
    /**
     * @var int
     */
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    /**
     * @var int
     */
    const REQUEST_TIMEOUT = 408;
    /**
     * @var int
     */
    const CONFLICT = 409;
    /**
     * @var int
     */
    const GONE = 410;
    /**
     * @var int
     */
    const LENGTH_REQUIRED = 411;
    /**
     * @var int
     */
    const PRECONDITION_FAILED = 412;
    /**
     * @var int
     */
    const REQUEST_ENTITY_TOO_LARGE = 413;
    /**
     * @var int
     */
    const REQUEST_URI_TOO_LONG = 414;
    /**
     * @var int
     */
    const UNSUPPORTED_MEDIA_TYPE = 415;
    /**
     * @var int
     */
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /**
     * @var int
     */
    const EXPECTATION_FAILED = 417;

    //.........................................................................
    //. Server Errors (5xx)
    //.........................................................................

    /**
     * @var int
     */
    const INTERNAL_SERVER_ERROR = 500;
    /**
     * @var int
     */
    const NOT_IMPLEMENTED = 501;
    /**
     * @var int
     */
    const BAD_GATEWAY = 502;
    /**
     * @var int
     */
    const SERVICE_UNAVAILABLE = 503;
    /**
     * @var int
     */
    const GATEWAY_TIMEOUT = 504;

    /**
     * @var int
     */
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @var int
     */
    const BANDWIDTH_LIMIT_EXCEEDED = 509;

    /**
     * Gets or sets HTTP headers to the request or response. All headers
     * are included immediately after the HTTP protocol definition during
     * transmission. This method provides a simple array or key/value
     * interface to the headers.
     *
     * @param   mixed  $key   Key or array of key/value pairs to set
     * @param   string $value Value to set to the supplied key
     * @return  mixed
     */
    public function headers($key = null, $value = null);

    /**
     * Renders the HTTP_Interaction to a string, producing
     *  - Protocol
     *  - Headers
     *  - Body
     *
     * @return  string
     */
    public function render();

}

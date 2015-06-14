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
interface HttpMessage
{

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

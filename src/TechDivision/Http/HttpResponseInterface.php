<?php
/**
 * \TechDivision\Http\HttpResponseInterface
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */

namespace TechDivision\Http;

/**
 * Interface HttpResponseInterface
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
interface HttpResponseInterface
{

    /**
     * Set's the default response headers to response
     *
     * @param array $headers The default headers array
     *
     * @return void
     */
    public function setDefaultHeaders(array $headers);

    /**
     * Return's default headers array
     *
     * @return array
     */
    public function getDefaultHeaders();

    /**
     * Add's a header information got from connection
     *
     * @param string $name  The header name
     * @param string $value The headers value
     *
     * @return void
     */
    public function addHeader($name, $value);

    /**
     * Check's if header exists by given name
     *
     * @param string $name The header name to check
     *
     * @return boolean
     */
    public function hasHeader($name);

    /**
     * Return's header value by given name
     *
     * @param string $name The header name
     *
     * @return string|null
     */
    public function getHeader($name);
    
    /**
     * Removes the header with the passed name.
     * 
     * @param string $name Name of the header to remove
     * 
     * @return void
     */
    public function removeHeader($name);

    /**
     * Return's all headers as array
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Set's all headers by given array
     *
     * @param array $headers The headers to set
     *
     * @return void
     */
    public function setHeaders(array $headers);

    /**
     * Return's all headers as string
     *
     * @return string
     */
    public function getHeaderString();

    /**
     * Reset's the stream resource pointing to body content
     *
     * @param resource $bodyStream The body content stream resource
     *
     * @return void
     */
    public function setBodyStream($bodyStream);

    /**
     * Return's the stream resource pointing to body content
     *
     * @return resource The body content stream resource
     */
    public function getBodyStream();

    /**
     * Append's body stream with content
     *
     * @param string $content The content to append
     *
     * @return int
     */
    public function appendBodyStream($content);

    /**
     * Reset the body stream
     *
     * @return void
     */
    public function resetBodyStream();

    /**
     * Copies a source stream to body stream
     *
     * @param resource $sourceStream The file pointer to source stream
     * @param int      $maxlength    The max length to read from source stream
     * @param int      $offset       The offset from source stream to read
     *
     * @return int the total count of bytes copied.
     */
    public function copyBodyStream($sourceStream, $maxlength = null, $offset = null);

    /**
     * Set's the http response status code
     *
     * @param int $code The status code to set
     *
     * @return void
     */
    public function setStatusCode($code);

    /**
     * Return's the response status code
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Splits status message into status code and reason phrase and sets it.
     *
     * @param string $status The status code + reason phrase in one string
     *
     * @return void
     */
    public function setStatus($status);

    /**
     * Set's the status reason phrase
     *
     * @param string $statusReasonPhrase The reason phrase
     *
     * @return void
     */
    public function setStatusReasonPhrase($statusReasonPhrase);

    /**
     * Return's the status phrase based on the status code
     *
     * @return string
     */
    public function getStatusReasonPhrase();

    /**
     * Return's the http version of the response
     *
     * @return string
     */
    public function getVersion();

    /**
     * Set's the http response version
     *
     * @param string $version The version to set (e.g. HTTP/1.1)
     *
     * @return void
     */
    public function setVersion($version);

    /**
     * Initialises the response object to default properties
     *
     * @return void
     */
    public function init();

    /**
     * Returns http response status line
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
     * @return string
     */
    public function getStatusLine();

    /**
     * Set's state of response
     *
     * @param int $state The state value
     *
     * @return void
     */
    public function setState($state);

    /**
     * Return's the current state
     *
     * @return int
     */
    public function getState();

    /**
     * Compares current state with given state
     *
     * @param int $state The state to compare with
     *
     * @return bool Wheater state is equal (true) or not (false)
     */
    public function hasState($state);

    /**
     * Add's the cookie by name to the cookies array
     *
     * @param HttpCookieInterface $cookie The cookie object
     *
     * @return void
     */
    public function addCookie(HttpCookieInterface $cookie);

    /**
     * Just returns the array of cookie objects
     *
     * @return array
     */
    public function getCookies();

    /**
     * Check if response has specific cookie
     *
     * @param string $name The name of the cookie to check for
     *
     * @return bool
     */
    public function hasCookie($name);

    /**
     * Get cookie by given name
     *
     * @param string $name The cookies name to get
     *
     * @return \TechDivision\Http\HttpCookie|void
     */
    public function getCookie($name);

    /**
     * Resets the whole cookies array by another array collection of cookie instances
     *
     * @param array $cookies The array of Cookie instances
     *
     * @return void
     */
    public function setCookies(array $cookies);
}

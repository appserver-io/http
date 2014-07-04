<?php
/**
 * \TechDivision\Http\HttpRequestInterface
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

use TechDivision\Connection\ConnectionRequestInterface;

/**
 * Interface HttpRequestInterface
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
interface HttpRequestInterface extends ConnectionRequestInterface
{

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
     * Initialises the request object to default properties
     *
     * @return void
     */
    public function init();

    /**
     * Set's requested uri
     *
     * @param string $uri The requested uri to set
     *
     * @return void
     */
    public function setUri($uri);

    /**
     * Return's requested uri
     *
     * @return string
     */
    public function getUri();

    /**
     * Set's request method
     *
     * @param string $method The method to set
     *
     * @return void
     */
    public function setMethod($method);

    /**
     * Get's request method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set's parsed query string
     *
     * @param string $queryString The parsed query string
     *
     * @return void
     */
    public function setQueryString($queryString);

    /**
     * Return's query string
     *
     * @return string The query string
     */
    public function getQueryString();

    /**
     * Set's body stream file descriptor resource
     *
     * @param resource $bodyStream The body stream file descriptor resource
     *
     * @return void
     */
    public function setBodyStream($bodyStream);

    /**
     * Return's body stream file descriptor resource
     *
     * @return resource|null
     */
    public function getBodyStream();

    /**
     * Return's the body content stored in body stream
     *
     * @return string
     */
    public function getBodyContent();

    /**
     * Append's body stream with content
     *
     * @param string $content The content to append
     *
     * @return int
     */
    public function appendBodyStream($content);

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
     * Returns the http request version.
     *
     * @return string The http request version
     */
    public function getVersion();

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
     * Check if request has specific cookie
     *
     * @param string $name The name of the cookie to check for
     *
     * @return bool
     */
    public function hasCookie($name);

    /**
     * Resets the whole cookies array by another array collection of cookie instances
     *
     * @param array $cookies The array of cookie instances
     *
     * @return void
     */
    public function setCookies(array $cookies);

    /**
     * Get cookie by given name
     *
     * @param string $name The cookies name to get
     *
     * @return \TechDivision\Http\HttpCookie|void
     */
    public function getCookie($name);

    /**
     * Set's specific http version
     *
     * @param string $version The version e.g. HTTP/1.1
     *
     * @return void
     */
    public function setVersion($version);

    /**
     * Set's a parameter given in query string
     *
     * @param string $param The param key
     * @param string $value The param value
     *
     * @return void
     */
    public function setParam($param, $value);

    /**
     * Return's a param value by given key
     *
     * @param string $param The param key
     *
     * @return string The param value
     */
    public function getParam($param);

    /**
     * Return's the array of all params
     *
     * @return array
     */
    public function getParams();

    /**
     * Set's the array of all params
     *
     * @param array $params The params array to set
     *
     * @return array
     */
    public function setParams($params);

    /**
     * Returns a part object by given name
     *
     * @param string $name The name of the form part
     *
     * @return \TechDivision\Http\HttpPart
     */
    public function getPart($name);

    /**
     * Returns the parts collection as array
     *
     * @return array A collection of HttpPart objects
     */
    public function getParts();

    /**
     * adds a part to the parts collection
     *
     * @param \TechDivision\Http\HttpPart $part A form part object
     * @param string                      $name A manually defined name
     *
     * @return void
     */
    public function addPart(HttpPart $part, $name = null);
}

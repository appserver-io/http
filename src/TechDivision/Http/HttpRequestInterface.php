<?php
/**
 * \TechDivision\Http\HttpRequestInterface
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
 * Interface HttpRequestInterface
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
interface HttpRequestInterface
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
     * Return's the real path to requested uri
     *
     * @return string
     */
    public function getRealPath();

    /**
     * Set's document root
     *
     * @param string $documentRoot The document root
     *
     * @return void
     */
    public function setDocumentRoot($documentRoot);

    /**
     * Return's the document root
     *
     * @return string
     */
    public function getDocumentRoot();

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
     * Set's parsed query string
     *
     * @param string $queryString The parsed query string
     *
     * @return void
     */
    public function setQueryString($queryString);

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
     * Set's specific http version
     *
     * @param string $version The version e.g. HTTP/1.1
     *
     * @return void
     */
    public function setVersion($version);
}

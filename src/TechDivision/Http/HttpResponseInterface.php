<?php
/**
 * \TechDivision\Http\HttpResponseInterface
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
     * Return's the mime type of response data
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Set's the specific mime type
     *
     * @param string $mimeType The mime type to set
     *
     * @return void
     */
    public function setMimeType($mimeType);

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
     * Set's the default server signature (e.g. phpWebServer/0.1.0)
     * This will be sent via "Server: phpWebServer/0.1.0" headers
     *
     * @param string $serverSignature The server signature
     *
     * @return void
     */
    public function setServerSignature($serverSignature);

    /**
     * Return's the server signature
     *
     * @return string
     */
    public function getServerSignature();
}

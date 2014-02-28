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
 */
interface HttpResponseInterface
{

    /**
     * Add's a header information
     *
     * @param string $name  The header name
     * @param string $value The headers value
     *
     * @return void
     */
    public function addHeader($name, $value);

    public function getHeader($name);

    public function getHeaders();

    public function setHeaders(array $headers);

    public function getHeaderString();

    /**
     * Reset's the stream resource pointing to body content
     *
     * @param resource $bodyStream The body content stream resource
     */
    public function setBodyStream($bodyStream);

    /**
     * Return's the stream resource pointing to body content
     *
     * @return resource The body content stream resource
     */
    public function getBodyStream();

    public function getMimeType();

    public function setMimeType($mimeType);

    public function setStatusCode($code);

    public function getStatusCode();

    public function getStatusReasonPhrase();

    public function getStatus();

    public function getVersion();

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

}


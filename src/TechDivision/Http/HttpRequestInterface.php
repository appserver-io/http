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

    public function getHeader($name);

    public function getHeaders();

    public function setHeaders(array $headers);

    public function getRealPath();

    public function setDocumentRoot($documentRoot);

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

    public function setMethod($method);

    public function setQueryString($queryString);

    public function setBodyStream($bodyStream);

    public function getBodyStream();

    public function setVersion($version);

}


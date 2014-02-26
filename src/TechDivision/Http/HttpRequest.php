<?php
/**
 * \TechDivision\Http\HttpRequest
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
 * Class HttpRequest
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HttpRequest implements RequestInterface
{
    /**
     * Hold's all headers got from http connection
     *
     * @var
     */
    protected $headers = array();

    /**
     * Hold's the http request method
     *
     * @var string
     */
    protected $method;

    /**
     * Hold's the protocol version
     *
     * @var string
     */
    protected $version;

    /**
     * Holds the uniform resource identifier
     *
     * @var string
     */
    protected $uri;

    /**
     * Hold's the file descriptor resource to body stream
     *
     * @var resource
     */
    protected $bodyStream;

    /**
     * Hold's the document root directory
     *
     * @var string
     */
    protected $documentRoot;


    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->bodyStream = fopen('php://memory', 'w+');
        $this->headers = array();
        $this->uri = null;
        $this->method = null;
        $this->version = null;
        $this->documentRoot = '/home/zelgerj/Repositories/TechDivision_WebServer/src/var/www';
        $this->queryString = null;
    }

    /**
     * Add's a header information got from connection
     *
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Check's if header exists by given name
     *
     * @param string $name The header name to check
     *
     * @return boolean
     */
    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * Return's header by given name
     *
     * @param string $name The header name to get
     *
     * @return string
     * @throws HttpException
     */
    public function getHeader($name)
    {
        if (!array_key_exists($name, $this->headers)) {
            throw new HttpException("Request header not found '$name'");
        }
        return $this->headers[$name];
    }

    /**
     * Return's all headers as array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Resets all headers by given array
     *
     * @param array $headers The headers array
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Set's the uri
     *
     * @param string $uri The uri
     *
     * @return void
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Set's the method
     *
     * @param string $method The http method
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Return's requested uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Return's the real path to file system
     *
     * @return string
     */
    public function getRealPath()
    {
        return $this->getDocumentRoot() . $this->getUri();
    }

    /**
     * Return's the document root
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * Set's query string
     *
     * @param string $queryString The requests query string
     *
     * @return voids
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Reset's the stream resource pointing to body content
     *
     * @param resource $bodyStream The body content stream resource
     */
    public function setBodyStream($bodyStream)
    {
        $this->bodyStream = $bodyStream;
    }

    /**
     * Return's the stream resource pointing to body content
     *
     * @return resource The body content stream resource
     */
    public function getBodyStream()
    {
        return $this->bodyStream;
    }

    /**
     * Set's the http request version
     *
     * @param string $version The http request version
     *
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

}


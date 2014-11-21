<?php

/**
 * AppserverIo\Http\HttpRequest
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
 * @package   Http
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2014 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 */

namespace AppserverIo\Http;

use AppserverIo\Psr\HttpMessage\CookieInterface;
use AppserverIo\Psr\HttpMessage\RequestInterface;

/**
 * Class HttpRequest
 *
 * @category  Library
 * @package   Http
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2014 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 */
class HttpRequest implements RequestInterface
{
    /**
     * Hold's all headers got from http connection
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Hold's all parsed cookie objects as array collection
     *
     * @var array
     */
    protected $cookies = array();

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
     * Hold's the request parameters
     *
     * @var array
     */
    protected $params = array();

    /**
     * Hold's the queryString parameters
     *
     * @var array
     */
    protected $queryString = '';

    /**
     * Holds collection of parts from multipart form data
     *
     * @var array A collection of HttpPart Objects
     */
    protected $parts = array();

    /**
     * Init's the body stream
     *
     * @return void
     */
    protected function resetBodyStream()
    {
        // if body stream exists close it
        if (is_resource($this->bodyStream)) {
            fclose($this->bodyStream);
        }
        $this->setBodyStream(fopen('php://memory', 'w+'));
    }

    /**
     * Constructs the request object
     */
    public function __construct()
    {
        $this->resetBodyStream();
    }

    /**
     * Initialises the request object to default properties
     *
     * @return void
     */
    public function init()
    {
        // init body stream
        $this->resetBodyStream();

        // init default response properties
        $this->headers = array();
        $this->params = array();
        $this->uri = null;
        $this->method = null;
        $this->version = null;
        $this->cookies = array();
        $this->parts = array();

        // Query string is always present, even if it is empty
        $this->queryString = '';

        return $this;
    }

    /**
     * Add's a header information got from connection
     *
     * @param string $name  The header name
     * @param string $value The headers value
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        // normalize header names in case of 'Content-type' into 'Content-Type'
        $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));

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
        return isset($this->headers[$name]);
    }

    /**
     * Return's header by given name
     *
     * @param string $name The header name to get
     *
     * @return string|null
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
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
     * Add's the cookie by name to the cookies array
     *
     * @param CookieInterface $cookie The cookie object
     *
     * @return void
     */
    public function addCookie(CookieInterface $cookie)
    {
        // add's the cookie by name to the cookies array
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * Check if request has specific cookie
     *
     * @param string $name The name of the cookie to check for
     *
     * @return bool
     */
    public function hasCookie($name)
    {
        // check if request has specific cookie
        return (isset($this->cookies[$name]) &&  $this->cookies[$name]->getName() === $name);
    }

    /**
     * Just returns the array of cookie objects
     *
     * @return array
     */
    public function getCookies()
    {
        // just returns the array of cookie objects
        return $this->cookies;
    }

    /**
     * Get cookie by given name
     *
     * @param string $name The cookies name to get
     *
     * @return \AppserverIo\Http\HttpCookie|void
     */
    public function getCookie($name)
    {
        // check if has specific cookie
        if ($this->hasCookie($name)) {
            return $this->cookies[$name];
        }
    }

    /**
     * Resets the whole cookies array by another array collection of cookie instances
     *
     * @param array $cookies The array of Cookie instances
     *
     * @return void
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
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
     * Get's request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
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
     * Set's query string
     *
     * @param string $queryString The requests query string
     *
     * @return void
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Return's query string
     *
     * @return string The query string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Reset's the stream resource pointing to body content
     *
     * @param resource $bodyStream The body content stream resource
     *
     * @return void
     */
    public function setBodyStream($bodyStream)
    {
        // check if old body stream is still open
        if (is_resource($this->bodyStream)) {
            // close it before
            fclose($this->bodyStream);
        }
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
     * Return's the body content stored in body stream
     *
     * @return string
     */
    public function getBodyContent()
    {
        // init vars
        $bodyContent = "";
        $contentLength = $this->getHeader(HttpProtocol::HEADER_CONTENT_LENGTH);
        // just if we got a body content
        if ($contentLength > 0) {
            // set bodystream resource ref to var
            $bodyStream = $this->getBodyStream();
            // rewind pointer
            rewind($bodyStream);
            // returns whole body content by given content length
            $bodyContent =  fread($bodyStream, $contentLength);
        }
        return $bodyContent;
    }

    /**
     * Copies a source stream to body stream
     *
     * @param resource $sourceStream The file pointer to source stream
     * @param int      $maxlength    The max length to read from source stream
     * @param int      $offset       The offset from source stream to read
     *
     * @return int the total count of bytes copied.
     */
    public function copyBodyStream($sourceStream, $maxlength = null, $offset = null)
    {
        // check if offset is given without maxlength
        if ($offset && !$maxlength) {
            throw new \InvalidArgumentException('offset can not be without a maxlength');
        }

        // first rewind sourceStream if its seekable
        $sourceStreamMetaData = stream_get_meta_data($sourceStream);
        if ($sourceStreamMetaData['seekable']) {
            rewind($sourceStream);
        }

        if ($offset && $maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength, $offset);
        }
        if (!$offset && $maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength);
        }
        // and finally
        return stream_copy_to_stream($sourceStream, $this->getBodyStream());
    }

    /**
     * Append's body stream with content
     *
     * @param string $content The content to append
     *
     * @return int
     */
    public function appendBodyStream($content)
    {
        return fwrite($this->getBodyStream(), $content);
    }

    /**
     * Returns the http request version.
     *
     * @return string The http request version
     */
    public function getVersion()
    {
        return $this->version;
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

    /**
     * Set's a parameter given in query string
     *
     * @param string $param The param key
     * @param string $value The param value
     *
     * @return void
     */
    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
    }

    /**
     * Return's a param value by given key
     *
     * @param string $param The param key
     *
     * @return string|null The param value
     */
    public function getParam($param)
    {
        if (isset($this->params[$param])) {
            return $this->params[$param];
        }
    }

    /**
     * Return's the array of all params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set's the array of all params
     *
     * @param array $params The params array to set
     *
     * @return array
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Returns a part object by given name
     *
     * @param string $name The name of the form part
     *
     * @return \AppserverIo\Http\HttpPart
     */
    public function getPart($name)
    {
        if (array_key_exists($name, $this->parts)) {
            return $this->parts[$name];
        }
    }

    /**
     * Returns the parts collection as array
     *
     * @return array A collection of HttpPart objects
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * adds a part to the parts collection
     *
     * @param \AppserverIo\Http\HttpPart $part A form part object
     * @param string                     $name A manually defined name
     *
     * @return void
     */
    public function addPart(HttpPart $part, $name = null)
    {
        if (is_null($name)) {
            $name = $part->getName();
        }
        $this->parts[$name] = $part;
    }
}

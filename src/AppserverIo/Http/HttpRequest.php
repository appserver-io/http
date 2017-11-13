<?php

/**
 * \AppserverIo\Http\HttpRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */

namespace AppserverIo\Http;

use AppserverIo\Psr\HttpMessage\CookieInterface;
use AppserverIo\Psr\HttpMessage\RequestInterface;

/**
 * Class HttpRequest
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */
class HttpRequest implements RequestInterface
{
    /**
     * Holds all headers got from http connection
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Holds all parsed cookie objects as array collection
     *
     * @var array
     */
    protected $cookies = array();

    /**
     * Holds the http request method
     *
     * @var string
     */
    protected $method;

    /**
     * Holds the protocol version
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
     * Holds the file descriptor resource to body stream
     *
     * @var resource
     */
    protected $bodyStream;

    /**
     * Holds the request parameters
     *
     * @var array
     */
    protected $params = array();

    /**
     * Holds the queryString parameters
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
     * Inits the body stream
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
     * Normalizes header field name
     *
     * @param  string $name header field name
     * @return string
     */
    protected function normalizeHeaderName($name)
    {
        return ucwords(str_replace('_', '-', strtolower($name)), '-');
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
     * Adds a header information got from connection
     *
     * @param string $name  The header name
     * @param string $value The headers value
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->headers[$this->normalizeHeaderName($name)] = $value;
    }

    /**
     * Checks if header exists by given name
     *
     * @param string $name The header name to check
     *
     * @return boolean
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$this->normalizeHeaderName($name)]);
    }

    /**
     * Returns header by given name
     *
     * @param string $name The header name to get
     *
     * @return string|null
     */
    public function getHeader($name)
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$this->normalizeHeaderName($name)];
        }
    }

    /**
     * Returns all headers as array
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
     * Adds the cookie by name to the cookies array
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
     * Sets the uri
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
     * Sets the method
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
     * Gets request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns requested uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Sets query string
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
     * Returns query string
     *
     * @return string The query string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Resets the stream resource pointing to body content
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
     * Returns the stream resource pointing to body content
     *
     * @return resource The body content stream resource
     */
    public function getBodyStream()
    {
        return $this->bodyStream;
    }

    /**
     * Returns the body content stored in body stream
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
            $bodyContent = fread($bodyStream, $contentLength);
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
     * Appends body stream with content
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
     * Sets the http request version
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
     * Sets a parameter given in query string
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
     * Returns a param value by given key
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
     * Queries whether the request contains a parameter or not.
     *
     * @param boolean $param TRUE if the parameter is available, else FALSE
     *
     * @return void
     */
    public function hasParam($param)
    {
        return isset($this->params[$param]);
    }

    /**
     * Returns the array of all params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the array of all params
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

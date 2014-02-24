<?php

namespace TechDivision\Http;

class HttpRequest implements RequestInterface
{

    /**
     * Hold's all headers got from http connection
     *
     * @var array
     */
    protected $headers;

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
            throw new HttpException("Header not found '$name'");
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
     * Set's the stream resource pointing to body content
     *
     * @param resource $bodyStream The body content stream resource
     */
    public function setBodyStream($bodyStream)
    {
        $this->bodyStream = $bodyStream;
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


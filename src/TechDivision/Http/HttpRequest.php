<?php

namespace TechDivision\Http;

class HttpRequest implements RequestInterface
{

    /**
     * Hold's all headers got from http connection
     *
     * @var array
     */
    public $headers;

    /**
     * Stream resource holding the request body
     *
     * @var resource
     */
    public $body;

    /**
     * Hold's the http request method
     *
     * @var string
     */
    public $method;

    /**
     * Hold's the protocol version
     *
     * @var string
     */
    public $protocolVersion;

    /**
     * Holds the uniform resource identifier
     *
     * @var string
     */
    public $uri;

    /**
     * Hold's the file descriptor resource to body stream
     *
     * @var resource
     */
    public $bodyStream;

    /**
     * Add's a header information got from connection
     *
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value)
    {

    }
}


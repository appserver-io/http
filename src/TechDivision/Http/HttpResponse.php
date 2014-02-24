<?php

namespace TechDivision\Http;

class HttpResponse implements ResponseInterface
{

    protected $mimeType;
    protected $bodyStream;

    public function __construct($welcomeFilename)
    {
        $this->bodyStream = fopen('php://memory', 'w+');

        $welcomenFileStream = fopen($welcomeFilename, 'r');
        stream_filter_prepend($welcomenFileStream, "zlib.deflate", STREAM_FILTER_READ);
        stream_copy_to_stream($welcomenFileStream, $this->bodyStream);

        $this->mimeType = 'text/html';
    }

    public function getHeaderString()
    {
        return "HTTP/1.1 200 OK" . PHP_EOL .
            "Content-Type: " . $this->getMimeType() . PHP_EOL .
            "Content-Length: " . ftell($this->getBodyStream()) . PHP_EOL .
            "Content-Encoding: deflate" . PHP_EOL .
            "Connection: close" . PHP_EOL .
            PHP_EOL;
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

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
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
            throw new HttpException("Response header not found '$name'");
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
}

<?php

namespace TechDivision\Http;

interface ResponseInterface
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

}


<?php

namespace TechDivision\Http;

interface RequestInterface
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

    public function setUri($uri);

    public function setMethod($method);

    public function setQueryString($queryString);

    public function setBodyStream($bodyStream);

    public function getBodyStream();

    public function setVersion($version);
}


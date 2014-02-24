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

}


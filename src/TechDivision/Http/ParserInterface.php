<?php

namespace TechDivision\Http;

interface ParserInterface
{
    /**
     * Parses the start line
     *
     * @param string $line The start line
     * @return void
     * @throws
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.1
     */
    public function parseStartLine($line);

    /**
     * @param string $line The line defining a http request header
     *
     * @return mixed
     */
    public function parseHeaderLine($line);

    /**
     * Parse headers in a proper way
     *
     * @param string $messageHeaders The message headers
     *
     * @return void
     * @throws \TechDivision\Http\HttpException
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
     */
    public function parseHeaders($messageHeaders);

    /**
     * Return's the request instance to pass parsed content to
     *
     * @return \TechDivision\Http\RequestInterface
     */
    public function getRequest();

    /**
     * Return's the response instance
     *
     * @return \TechDivision\Http\ResponseInterface
     */
    public function getResponse();
}


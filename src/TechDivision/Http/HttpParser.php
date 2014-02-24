<?php

namespace TechDivision\Http;

class HttpParser implements ParserInterface
{

    /**
     * Hold's the request instance to fill up.
     *
     * @var \TechDivision\Http\RequestInterface
     */
    protected $request;

    /**
     * Set's the given request implementation
     *
     * @param \TechDivision\Http\RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Return's the request instance to pass parsed content to
     *
     * @return \TechDivision\Http\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Parses the start line
     *
     * @param string $line The start line
     * @return void
     * @throws
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.1
     */
    public function parseStartLine($line)
    {
        if (!preg_match(
            "/(OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)\s(.*)\s(HTTP\/1\.0|HTTP\/1\.1)/",
            $line,
            $matches
        )
        ) {
            throw new HttpException('Bad request.');
        }
        // grab http version and request method from first request line.
        list($reqMethod, $reqUri, $reqVersion) = $matches;
        // fill up request object
        $this->getRequest()->setMethod($reqMethod);
        $this->getRequest()->setUri($reqUri);
        $this->getRequest()->setVersion($reqVersion);
    }

    /**
     * Parses a http header line
     *
     * @param string $line The line defining a http request header
     *
     * @return mixed
     * @throws \TechDivision\Http\HttpException
     */
    public function parseHeaderLine($line)
    {
        // extract header info
        $extractedHeaderInfo = explode(':', trim(strtolower($line)));
        if (!$extractedHeaderInfo) {
            throw new HttpException('Wrong header format.');
        }
        list($headerName, $headerValue) = $extractedHeaderInfo;
        // add request header
        $this->getRequest()->addHeader($headerName, $headerValue);
    }

}

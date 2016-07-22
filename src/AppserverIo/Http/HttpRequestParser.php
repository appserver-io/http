<?php

/**
 * \AppserverIo\Http\HttpRequestParser
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

use AppserverIo\Psr\HttpMessage\PartInterface;
use AppserverIo\Psr\HttpMessage\RequestInterface;
use AppserverIo\Psr\HttpMessage\ResponseInterface;

/**
 * Class HttpRequestParser
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */
class HttpRequestParser implements HttpRequestParserInterface
{

    /**
     * Holds the request instance to prepare
     *
     * @var \AppserverIo\Psr\HttpMessage\RequestInterface
     */
    protected $request;

    /**
     * Holds the response instance to prepare
     *
     * @var \AppserverIo\Psr\HttpMessage\ResponseInterface
     */
    protected $response;

    /**
     * Holds the query parser instance
     *
     * @var \AppserverIo\Http\HttpQueryParser
     */
    protected $queryParser;

    /**
     * Normalizes uri in a very simple way.
     *
     * To avoide multiple XSS scripting vulnerabilities, each directory part of the URI has
     * to be filtered  for script embedding from links displayed on a server's web site.
     *
     * @param string $uri The uri to normalize
     *
     * @return string The normalized uri string
     * @throws \AppserverIo\Http\HttpException
     *
     * @static
     * @see http://tools.ietf.org/html/rfc3986
     */
    public static function normalizeUri($uri)
    {
        // init vars
        $normalizedPathElements = array(0 => '');
        $queryString = '';
        $uriWithoutQueryString = $uri;
        $directoryIndicator = 0;
        // split possible query string from uri
        if (($queryStringPos = strpos($uri, "?")) !== false) {
            $queryString = substr($uri, $queryStringPos);
            $uriWithoutQueryString = substr($uri, 0, $queryStringPos);
        }
        // get all path elements from uri
        $pathElements = explode('/', rawurldecode($uriWithoutQueryString));
        // count path elements
        $pathElementCount = count($pathElements);
        // init count variable
        $i = 1;
        // proceed path elements
        do {
            $pathElement = $pathElements[$i];
            // do not cover current dir indicators
            if ($pathElement === '.' || $pathElement === '') {
                // if last item was . or empty string add empty entry to new path element to keep trailing slash
                if ($i === $pathElementCount -1) {
                    $normalizedPathElements[$directoryIndicator + 1] = '';
                }
                continue;
            }
            // inc current dir indicator if no forwarding dir is given.
            if ($pathElement !== '..') {
                ++$directoryIndicator;
                // set new path elements array for normalized uri and escape HTML special chars
                $normalizedPathElements[$directoryIndicator] = htmlspecialchars($pathElement);
                // otherwise dec dir indicator
            } else {
                // if last item was . or empty string add empty entry to new path element to keep trailing slash
                if ($i === $pathElementCount -1) {
                    $normalizedPathElements[$directoryIndicator + 1] = '';
                }
                unset($normalizedPathElements[$directoryIndicator]);
                --$directoryIndicator;
            }
            // check if indicator got invalid
            if ($directoryIndicator < 0) {
                throw new HttpException('Bad request.', 400);
            }
        } while (++$i < $pathElementCount);
        // return full normalized uri
        return implode('/', $normalizedPathElements) . $queryString;
    }

    /**
     * Set's the given request and response class names
     *
     * @param \AppserverIo\Psr\HttpMessage\RequestInterface  $request  The request instance
     * @param \AppserverIo\Psr\HttpMessage\ResponseInterface $response The response instance
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        // add request and response
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Will init the request parser anew so it can be reused even when being persistent
     *
     * @return void
     */
    public function init()
    {
        // Init the query parser
        $this->getQueryParser()->clear();

        // Init request and response
        $this->getRequest()->init();
        $this->getResponse()->init();
    }

    /**
     * Injects query parser instance
     *
     * @param HttpQueryParserInterface $queryParser The query parser instance
     *
     * @return void
     */
    public function injectQueryParser(HttpQueryParserInterface $queryParser)
    {
        // inject query parser
        $this->queryParser = $queryParser;
    }

    /**
     * Injects http part implementation
     *
     * @param \AppserverIo\Psr\HttpMessage\PartInterface $part The part implementation
     *
     * @return void
     */
    public function injectPart(PartInterface $part)
    {
        $this->part = $part;
    }

    /**
     * Return's a new instance of http part
     *
     * @return \AppserverIo\Http\HttpPart
     */
    public function getHttpPartInstance()
    {
        return $this->part->getInstance();
    }

    /**
     * Return's the query parser instance
     *
     * @return \AppserverIo\Http\HttpQueryParser
     */
    public function getQueryParser()
    {
        return $this->queryParser;
    }

    /**
     * Return's the request instance to pass parsed content to
     *
     * @return \AppserverIo\Psr\HttpMessage\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return's the response instance
     *
     * @return \AppserverIo\Psr\HttpMessage\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }


    /**
     * Parses the start line
     *
     * @param string $line The start line
     *
     * @return void
     * @throws \AppserverIo\Http\HttpException
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.1
     */
    public function parseStartLine($line)
    {
        // initialize the array with the matches
        $matches = array();
        $request = $this->getRequest();
        // validate start line
        if (!preg_match(
            "/(OPTIONS|GET|HEAD|PATCH|POST|PUT|DELETE|TRACE|CONNECT)\s(\/.*)\s(HTTP\/1\.0|HTTP\/1\.1)/",
            $line,
            $matches
        )
        ) {
            throw new HttpException('Bad request.', 400);
        }
        // grab http version and request method from first request line.
        list(, $reqMethod, $reqUri, $reqVersion) = $matches;
        // fill up request object
        $request->setMethod($reqMethod);
        $request->setUri(self::normalizeUri($reqUri));
        $request->setVersion($reqVersion);

        // parse query string
        if ($queryString = substr(strstr($reqUri, '?'), 1)) {
            $request->setQueryString($queryString);
            $this->getQueryParser()->parseStr($queryString);
        }
    }


    /**
     * Parse headers in a proper way
     *
     * @param string $messageHeaders The message headers
     *
     * @return void
     * @throws \AppserverIo\Http\HttpException
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
     */
    public function parseHeaders($messageHeaders)
    {
        // remove ending CRLF's before parsing
        $messageHeaders = trim($messageHeaders);
        // check if headers are empty
        if (strlen($messageHeaders) === 0) {
            throw new HttpException('Missing headers');
        }
        // delimit headers by CRLF
        $headerLines = explode("\r\n", $messageHeaders);
        // iterate all headers
        foreach ($headerLines as $headerLine) {
            // parse header line
            $this->parseHeaderLine($headerLine);
        }
    }

    /**
     * Parses a http header line
     *
     * @param string $line The line defining a http request header
     *
     * @return mixed
     * @throws \AppserverIo\Http\HttpException
     */
    public function parseHeaderLine($line)
    {
        // extract header info
        $extractedHeaderInfo = explode(HttpProtocol::HEADER_SEPARATOR, trim($line));

        if ((!$extractedHeaderInfo) || ($extractedHeaderInfo[0] === $line)) {
            throw new HttpException('Wrong header format');
        }

        // split name and value
        list($headerName, $headerValue) = $extractedHeaderInfo;

        // add header
        $this->getRequest()->addHeader(trim($headerName), trim($headerValue));

        // check if we got a cookie header name so parse the cookie
        if ($headerName === HttpProtocol::HEADER_COOKIE) {
            $this->parseCookieHeaderValue($headerValue);
        }
    }

    /**
     * Parses the http set cookie header
     *
     * @param string $headerValue The header value with cookie info in it
     *
     * @return void
     */
    public function parseCookieHeaderValue($headerValue)
    {
        $request = $this->getRequest();
        // parse cookies and iterate over
        foreach (explode(';', $headerValue) as $cookieStr) {
            // check if cookieStr is no just a empty str
            if (strlen($cookieStr) > 0) {
                // add cookie object to request if the cookie string can be parsed properly
                if ($cookie = HttpCookie::createFromRawSetCookieHeader($cookieStr)) {
                    $request->addCookie($cookie);
                }
            }
        }
    }

    /**
     * Parse multipart form data
     *
     * @param string $content The content to parse
     *
     * @return void
     */
    public function parseMultipartFormData($content)
    {
        // initialize the array with the matches
        $matches = array();
        // get request ref to local function context
        $request = $this->getRequest();
        // grab multipart boundary from content type header
        preg_match('/boundary=(.+)$/', $this->getRequest()->getHeader(HttpProtocol::HEADER_CONTENT_TYPE), $matches);
        // check if boundary is not set
        if (!isset($matches[1])) {
            return;
        }
        // get boundary
        $boundary = $matches[1];
        // split content by boundary
        $blocks = preg_split("/-+$boundary/", $content);
        // get rid of last -- element
        array_pop($blocks);
        // loop data blocks
        foreach ($blocks as $block) {
            // of block is empty continue with next one
            if (empty($block)) {
                continue;
            }

            // check if filename is given
            // todo: refactor file part generating
            if (strpos($block, '; filename="') !== false) {
                // init new part instance
                $part = $this->getHttpPartInstance();
                // seperate headers from body
                $partHeaders = strstr($block, "\n\r\n", true);
                $partBody = ltrim(strstr($block, "\n\r\n"));
                // parse part headers
                foreach (explode("\n", $partHeaders) as $h) {
                    $h = explode(':', $h, 2);
                    if (isset($h[1])) {
                        $part->addHeader($h[0], trim($h[1]));
                    }
                }
                // match name and filename
                if (preg_match("/name=\"([^\"]*)\"; filename=\"([^\"]*)\".*$/s", $partHeaders, $matches) !== 0) {
                    // set name
                    $part->setName($matches[1]);
                    // set given filename is is set
                    $part->setFilename($matches[2]);
                    // put content to part
                    $part->putContent(preg_replace('/.' . PHP_EOL . '$/', '', $partBody));
                    // add the part instance to request
                    $request->addPart($part);
                }
                // parse all other fields as normal key value pairs
            } else {
                // match "name" and optional value in between newline sequences
                if (preg_match('/name=\"([^\"]*)\"[\r\n]+([^\r\n]*)?/', $block, $matches) !== 0) {
                    $this->getQueryParser()->parseKeyValue($matches[1], $matches[2]);
                }
            }
        }
    }
}

<?php
/**
 * \TechDivision\Http\HttpRequestParser
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */

namespace TechDivision\Http;

/**
 * Class HttpRequestParser
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
class HttpRequestParser implements HttpRequestParserInterface
{

    /**
     * Holds the request instance to prepare
     *
     * @var \TechDivision\Http\HttpRequestInterface
     */
    protected $request;

    /**
     * Holds the response instance to prepare
     *
     * @var \TechDivision\Http\HttpResponseInterface
     */
    protected $response;

    /**
     * Holds the query parser instance
     *
     * @var \TechDivision\Http\HttpQueryParser
     */
    protected $queryParser;

    /**
     * Set's the given request and response class names
     *
     * @param \TechDivision\Http\HttpRequestInterface  $request  The request instance
     * @param \TechDivision\Http\HttpResponseInterface $response The response instance
     */
    public function __construct(HttpRequestInterface $request, HttpResponseInterface $response)
    {
        // add request and response
        $this->request = $request;
        $this->response = $response;
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
     * @param HttpPartInterface $part The part implementation
     *
     * @return void
     */
    public function injectPart(HttpPartInterface $part)
    {
        $this->part = $part;
    }

    /**
     * Return's a new instance of http part
     *
     * @return \TechDivision\Http\HttpPart
     */
    public function getHttpPartInstance()
    {
        return $this->part->getInstance();
    }

    /**
     * Return's the query parser instance
     *
     * @return \TechDivision\Http\HttpQueryParser
     */
    public function getQueryParser()
    {
        return $this->queryParser;
    }

    /**
     * Return's the request instance to pass parsed content to
     *
     * @return \TechDivision\Http\HttpRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return's the response instance
     *
     * @return \TechDivision\Http\HttpResponseInterface
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
     * @throws \TechDivision\Http\HttpException
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.1
     */
    public function parseStartLine($line)
    {
        $request = $this->getRequest();
        if (!preg_match(
            "/(OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)\s(.*)\s(HTTP\/1\.0|HTTP\/1\.1)/",
            $line,
            $matches
        )
        ) {
            throw new HttpException('Bad request.');
        }
        // grab http version and request method from first request line.
        list($reqStartLine, $reqMethod, $reqUri, $reqVersion) = $matches;
        // fill up request object
        $request->setMethod($reqMethod);
        $request->setUri($reqUri);
        $request->setVersion($reqVersion);

        // parse query string
        if ($queryString = parse_url($reqUri, PHP_URL_QUERY)) {
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
     * @throws \TechDivision\Http\HttpException
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
     * @throws \TechDivision\Http\HttpException
     */
    public function parseHeaderLine($line)
    {
        // extract header info
        $extractedHeaderInfo = explode(': ', trim($line));
        if (!$extractedHeaderInfo) {
            throw new HttpException('Wrong header format');
        }

        // split name and value
        list($headerName, $headerValue) = $extractedHeaderInfo;

        // normalize header names in case of 'Content-type' into 'Content-Type'
        $headerName = str_replace(' ', '-', ucwords(str_replace('-', ' ', $headerName)));

        // add header
        $this->getRequest()->addHeader(trim($headerName), trim($headerValue));
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
        // get request ref to local function context
        $request = $this->getRequest();
        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $this->getRequest()->getHeader(HttpProtocol::HEADER_CONTENT_TYPE), $matches);
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
        foreach ($blocks as $id => $block) {
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
                foreach (explode("\n", $partHeaders) as $i => $h) {
                    $h = explode(':', $h, 2);
                    if (isset($h[1])) {
                        $part->addHeader($h[0], trim($h[1]));
                    }
                }
                // match name and filename
                preg_match("/name=\"([^\"]*)\"; filename=\"([^\"]*)\".*$/s", $partHeaders, $matches);
                // set name
                $part->setName($matches[1]);
                // set given filename
                $part->setFilename($matches[2]);
                // put content to part
                $part->putContent(preg_replace('/.' . PHP_EOL . '$/', '', $partBody));
                // add the part instance to request
                $request->addPart($part);
                // parse all other fields as normal key value pairs
            } else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?[\n|\r]+$/', $block, $matches);
                // if no value given set null
                if (!isset($matches[2])) {
                    $matches[2] = null;
                }
                $this->getQueryParser()->parseKeyValue($matches[1], $matches[2]);
            }
        }
    }
}

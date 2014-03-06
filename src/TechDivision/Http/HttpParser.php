<?php
/**
 * \TechDivision\Http\HttpParser
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
 * Class HttpParser
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
class HttpParser implements HttpParserInterface
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
        // instantiate query parser
        $this->queryParser = new HttpQueryParser();
        // add request and response
        $this->request = $request;
        $this->response = $response;
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
        if ($queryString = parse_url($request->getUri(), PHP_URL_QUERY)) {
            $request->setQueryString($queryString);
            $this->getQueryParser()->parseStr($queryString);
        }

        // todo: read out config for all file handle extensions not just hardcore php
        $fileHandlerExtension = '.php';
        // check if fileHandler type are present in uri
        if (strpos($request->getUri(), $fileHandlerExtension) !== false) {
            // check where the script position ends
            $scriptStrEndPos = strpos($request->getUri(), $fileHandlerExtension) + strlen($fileHandlerExtension);
            // parse script name
            $scriptName = substr($request->getUri(), 0, $scriptStrEndPos);
            // set script name to request object
            $request->setScriptName($scriptName);
            // parse path info if exists in uri
            if (($pathInfo = substr(str_replace('?' . $request->getQueryString(), '', $request->getUri()), $scriptStrEndPos)) !== false) {
                // set path info to request object
                $request->setPathInfo($pathInfo);
            }
            /**
             * it's intended to not set ScriptFilename and PathTranslated here because the request doesn't
             * care about document root stuff. so this is set by the connection handler!
             */
            // todo: check and implement ORIG_PATH_INFO server var
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
        list($headerName, $headerValue) = $extractedHeaderInfo;
        // add request header with name lowercase for further compare functions
        $this->getRequest()->addHeader($headerName, $headerValue);
    }
}

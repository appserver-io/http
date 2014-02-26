<?php
/**
 * \TechDivision\Http\HttpResponse
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace TechDivision\Http;

/**
 * Class HttpResponse
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HttpResponse implements HttpResponseInterface
{

    /**
     * Defines response http version
     *
     * @var string
     */
    protected $version;

    /**
     * Defines the response status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Defines the response reason phrase
     *
     * @var string
     */
    protected $statusReasonPhrase;

    /**
     * Defines the response mime type
     *
     * @var string
     */
    protected $mimeType = "text/plain";

    /**
     * Defines the response body stream
     *
     * @var resource
     */
    protected $bodyStream;

    /**
     * Hold's all headers
     *
     * @var
     */
    protected $headers;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        // init default response properties
        $this->statusCode = 200;
        $this->version = 'HTTP/1.1';;
        $this->statusReasonPhrase = "OK";
        $this->mimeType = "text/plain";
        $this->bodyStream = fopen('php://memory', 'w+');
        // init default headers
        $this->addHeader(HttpProtocol::HEADER_CONNECTION, "close");
        $this->addHeader(HttpProtocol::HEADER_SERVER, "phpWebserver/0.1.0");
    }

    public function getHeaderString()
    {
        // check if content length must be set
        fseek($this->getBodyStream(), 0, SEEK_END);
        $contentLength = ftell($this->getBodyStream());

        if ($contentLength > 0) {
            $this->addHeader(HttpProtocol::HEADER_CONTENT_TYPE, $this->getMimeType());
            $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, $contentLength);
        }

        $headerString = '';

        // enhance headers
        foreach ($this->getHeaders() as $headerName => $headerValue) {
            $headerString .= $headerName . ': ' . $headerValue . "\r\n";
        }

        // return with ending CRLF
        return $headerString . "\r\n";
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

    public function setStatusCode($code)
    {
        // set status code
        $this->statusCode = $code;
        // lookup reason phrase by code
        $this->statusReasonPhrase = HttpProtocol::getStatusReasonPhraseByCode($code);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getStatusReasonPhrase()
    {
        return $this->statusReasonPhrase;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Returns http response status line
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html
     * @return string
     */
    public function getStatusLine()
    {
        // Status-Line = HTTP-Version SP Status-Code SP Reason-Phrase CRLF
        return $this->getVersion() . ' ' . $this->getStatusCode() . ' ' . $this->getStatusReasonPhrase() . "\r\n";
    }
}


<?php
/**
 * \TechDivision\Http\HttpResponse
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
 * Class HttpResponse
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
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
     * Hold's the servers signature
     *
     * @var string
     */
    protected $serverSignature;

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

    /**
     * Initialises the response object to default properties
     *
     * @return void
     */
    public function init()
    {
        // if body stream exists close it
        if (is_resource($this->bodyStream)) {
            fclose($this->bodyStream);
        }
        // init body stream
        $this->bodyStream = fopen('php://memory', 'w+');

        // init default response properties
        $this->statusCode = 200;
        $this->version = 'HTTP/1.1';
        $this->statusReasonPhrase = "OK";
        $this->mimeType = "text/plain";

        // reset to default headers
        $this->setHeaders(array(
            HttpProtocol::HEADER_CONNECTION => "close",
            HttpProtocol::HEADER_SERVER => $this->getServerSignature()
        ));
    }

    /**
     * Return's all headers as string
     *
     * @return string
     */
    public function getHeaderString()
    {
        // check if content length must be set
        fseek($this->getBodyStream(), 0, SEEK_END);
        $contentLength = ftell($this->getBodyStream());

        if ($contentLength > 0) {
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
     *
     * @return void
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

    /**
     * Append's body stream with content
     *
     * @param string $content The content to append
     *
     * @return int
     */
    public function appendBodyStream($content)
    {
        return fwrite($this->getBodyStream(), $content);
    }

    /**
     * Copies a source stream to body stream
     *
     * @param resource $sourceStream The file pointer to source stream
     * @param int      $maxlength    The max length to read from source stream
     * @param int      $offset       The offset from source stream to read
     *
     * @return int the total count of bytes copied.
     */
    public function copyBodyStream($sourceStream, $maxlength = null, $offset = null)
    {
        return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength, $offset);
    }

    /**
     * Return's the mime type of response data
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set's the specific mime type
     *
     * @param string $mimeType The mime type to set
     *
     * @return void
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Add's a header information got from connection
     *
     * @param string $name  The header name
     * @param string $value The headers value
     *
     * @return void
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

    /**
     * Splits status message into status code and reason phrase and sets it.
     *
     * @param string $status The status code + reason phrase in one string
     *
     * @return void
     */
    public function setStatus($status)
    {
        // check if correct status line format is given
        if (preg_match('/(\d+)\s+(.*)/', $status, $matches) > 0) {
            $this->setStatusCode(trim($matches[1]));
            $this->setStatusReasonPhrase($matches[2]);
        }
    }

    /**
     * Set's the http response status code
     *
     * @param int $code The status code to set
     *
     * @return void
     */
    public function setStatusCode($code)
    {
        // set status code
        $this->statusCode = $code;
        // lookup reason phrase by code and set
        $this->setStatusReasonPhrase(HttpProtocol::getStatusReasonPhraseByCode($code));
    }

    /**
     * Set's the status reason phrase
     *
     * @param string $statusReasonPhrase The reason phrase
     *
     * @return void
     */
    public function setStatusReasonPhrase($statusReasonPhrase)
    {
        $this->statusReasonPhrase = $statusReasonPhrase;
    }

    /**
     * Return's the response status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return's the status phrase based on the status code
     *
     * @return string
     */
    public function getStatusReasonPhrase()
    {
        return $this->statusReasonPhrase;
    }

    /**
     * Return's the http version of the response
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set's the http response version
     *
     * @param string $version The version to set (e.g. HTTP/1.1)
     *
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Set's the default server signature (e.g. phpWebServer/0.1.0)
     * This will be sent via "Server: phpWebServer/0.1.0" headers
     *
     * @param string $serverSignature The server signature
     *
     * @return void
     */
    public function setServerSignature($serverSignature)
    {
        $this->serverSignature = $serverSignature;
    }

    /**
     * Return's the server signature
     *
     * @return string
     */
    public function getServerSignature()
    {
        return $this->serverSignature;
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

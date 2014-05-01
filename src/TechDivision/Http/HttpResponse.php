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

use TechDivision\Http\HttpProtocol;

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
    protected $version = 'HTTP/1.1';

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
     * Hold's the default headers
     *
     * @var array $defaultHeaders
     */
    protected $defaultHeaders = array();

    /**
     * Represent's the state
     *
     * @var int
     */
    protected $state;

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
        $this->bodyStream = fopen('php://memory', 'w+b');

        // init default response properties
        $this->statusCode = 200;
        $this->version = 'HTTP/1.1';
        $this->statusReasonPhrase = "OK";
        $this->mimeType = "text/plain";
        $this->state = HttpResponseStates::INITIAL;

        // reset to default headers
        $this->initHeaders();
    }

    /**
     * Initiates headers array by default headers array
     *
     * @return void
     */
    protected function initHeaders()
    {
        // set default headers
        $this->setHeaders($this->getDefaultHeaders());
    }

    /**
     * Set's the default response headers to response
     *
     * @param array $headers The default headers array
     *
     * @return void
     */
    public function setDefaultHeaders(array $headers)
    {
        $this->defaultHeaders = $headers;
    }

    /**
     * Return's default headers array
     *
     * @return array
     */
    public function getDefaultHeaders()
    {
        return $this->defaultHeaders;
    }

    /**
     * Return's current content length
     *
     * @return int
     */
    public function getContentLength()
    {
        // checkout for content length
        rewind($this->getBodyStream());
        fseek($this->getBodyStream(), 0, SEEK_END);
        return ftell($this->getBodyStream());
    }

    /**
     * Prepares the headers to ready for delivery
     *
     * @return void
     */
    public function prepareHeaders()
    {
        // check if status code is content-length relevant
        if ((int)$this->getStatusCode() < 300 || (int)$this->getStatusCode() > 399) {
            $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, $this->getContentLength());
        } else {
            $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, 0);
        }
    }

    /**
     * Return's all headers as string
     *
     * @return string
     */
    public function getHeaderString()
    {
        // initialize the string for the headers
        $headerString = '';
        // concatenate the headers to a string
        foreach ($this->getHeaders() as $headerName => $headerValue) {
            // take care for the Set-Cookie headers
            if (is_array($headerValue)) {
                foreach ($headerValue as $value) {
                    $headerString .= $headerName . ': ' . $value . "\r\n";
                }
            } else {
                $headerString .= $headerName . ': ' . $headerValue . "\r\n";
            }
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
        // check if old body stream is still open
        if (is_resource($this->bodyStream)) {
            // close it before
            fclose($this->bodyStream);
            // free it
            unset($this->bodyStream);
        }
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
     * Cleans the body stream
     *
     * @return void
     */
    public function unlinkBodyStream()
    {
        if (is_resource($this->bodyStream)) {
            // close it before
            fclose($this->bodyStream);
            // free it
            unset($this->bodyStream);
        }

        $this->bodyStream = fopen('php://memory', 'w+b');
    }


    /**
     * Return's the body content stored in body stream
     *
     * @return string
     */
    public function getBodyContent()
    {
        // set bodystream resource ref to var
        $bodyStream = $this->getBodyStream();
        fseek($bodyStream, 0, SEEK_END);
        $length = ftell($bodyStream);
        // rewind pointer
        rewind($bodyStream);
        // returns whole body content
        $content = fread($bodyStream, $length);
        return $content;
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
        if ($offset && $maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength, $offset);
        }
        if (!$offset && $maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength);
        }
        if (!$offset && !$maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream());
        }
    }

    /**
     * Adds a header information got from connection. We've to take care that headers
     * like Set-Cookie header can exist multiple times. To support this create an 
     * array that keeps the multiple header values.
     *
     * @param string  $name   The header name
     * @param string  $value  The headers value
     * @param boolean $append If TRUE and a header with the passed name already exists, the value will be appended
     *
     * @return void
     */
    public function addHeader($name, $value, $append = false)
    {

        // check if we've a Set-Cookie header to process
        if ($this->hasHeader($name) && $append === true) {
            
            // then check if we've already one cookie header available
            if (is_array($headerValue = $this->getHeader($name))) {
                $headerValue[] = $value;
            } else {
                $headerValue = array($headerValue, $value);
            }

            // if no cookie header simple add it
            $this->headers[$name] = $headerValue;

        } else {
            $this->headers[$name] = $value;
        }
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
     * Returns header by given name.
     *
     * @param string $name The header name to get
     *
     * @return mixed Usually a string, but can also be an array if we request the Set-Cookie header
     * @throws \TechDivision\Http\HttpException Is thrown if the requested header is not available
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name]) === false) {
            throw new HttpException("Response header '$name' not found");
        }
        return $this->headers[$name];
    }

    /**
     * Removes the header with the passed name.
     *
     * @param string $name Name of the header to remove
     *
     * @return void
     */
    public function removeHeader($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
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
     * Set's state of response
     *
     * @param int $state The state value
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Return's the current state
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Compares current state with given state
     *
     * @param int $state The state to compare with
     *
     * @return bool Wheater state is equal (true) or not (false)
     */
    public function hasState($state)
    {
        return ($this->state === $state);
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

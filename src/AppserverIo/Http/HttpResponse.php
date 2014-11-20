<?php

/**
 * AppserverIo\Http\HttpResponse
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
 * @package   Http
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2014 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 */

namespace AppserverIo\Http;

use AppserverIo\Psr\HttpMessage\CookieInterface;
use AppserverIo\Psr\HttpMessage\Protocol;
use AppserverIo\Psr\HttpMessage\ResponseInterface;

/**
 * Class HttpResponse
 *
 * @category  Library
 * @package   Http
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2014 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 */
class HttpResponse implements ResponseInterface
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
    protected $statusCode = 200;

    /**
     * Defines the response reason phrase
     *
     * @var string
     */
    protected $statusReasonPhrase = HttpProtocol::STATUS_REASONPHRASE_200;

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
    protected $headers = array();

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
    protected $state = HttpResponseStates::INITIAL;

    /**
     * The array containing the response cookies.
     *
     * @var array
     */
    protected $cookies = array();

    /**
     * Constructs the request object
     */
    public function __construct()
    {
        $this->resetBodyStream();
    }

    /**
     * Initialises the response object to default properties
     *
     * @return void
     */
    public function init()
    {
        // init body stream
        $this->resetBodyStream();

        // init default response properties
        $this->statusCode = 200;
        $this->version = 'HTTP/1.1';
        $this->statusReasonPhrase = HttpProtocol::STATUS_REASONPHRASE_200;
        $this->mimeType = "text/plain";
        $this->state = HttpResponseStates::INITIAL;
        $this->cookies = array();

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
        // check if status code is content-length relevant
        if ((int)$this->getStatusCode() < 300 || (int)$this->getStatusCode() > 399) {
            // checkout for content length
            rewind($this->getBodyStream());
            fseek($this->getBodyStream(), 0, SEEK_END);
            return ftell($this->getBodyStream());
        }
        return 0;
    }

    /**
     * Prepare's the headers for dispatch
     *
     * @return void
     */
    public function prepareHeaders()
    {
        // set current date before render it
        $this->addHeader(HttpProtocol::HEADER_DATE, gmdate(DATE_RFC822));

        // render content length to header
        $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, $this->getContentLength());
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

            // take care for manuel added headers with appending
            if (is_array($headerValue)) {
                foreach ($headerValue as $value) {
                    $headerString .= $headerName . HttpProtocol::HEADER_SEPARATOR . $value . "\r\n";
                }
            } else {
                $headerString .= $headerName . HttpProtocol::HEADER_SEPARATOR . $headerValue . "\r\n";
            }
        }

        // add set-cookie headers by cookie collection
        foreach ($this->getCookies() as $cookieName => $cookie) {
            $headerString .= HttpProtocol::HEADER_SET_COOKIE . HttpProtocol::HEADER_SEPARATOR . $cookie->__toString() . "\r\n";
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
     * Reset the body stream
     *
     * @return void
     */
    public function resetBodyStream()
    {
        if (is_resource($this->bodyStream)) {
            // close it before
            fclose($this->bodyStream);
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
        // init vars
        $content = "";
        // set bodystream resource ref to var
        $bodyStream = $this->getBodyStream();
        fseek($bodyStream, 0, SEEK_END);
        $length = ftell($bodyStream);
        // just in case we have length here
        if ($length > 0) {
            // rewind pointer
            rewind($bodyStream);
            // returns whole body content
            $content = fread($bodyStream, $length);
        }
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
        // check if offset is given without maxlength
        if ($offset && !$maxlength) {
            throw new \InvalidArgumentException('offset can not be without a maxlength');
        }

        // first rewind sourceStream if its seekable
        $sourceStreamMetaData = stream_get_meta_data($sourceStream);
        if ($sourceStreamMetaData['seekable']) {
            rewind($sourceStream);
        }

        if ($offset && $maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength, $offset);
        }
        if (!$offset && $maxlength) {
            return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength);
        }
        // and finally
        return stream_copy_to_stream($sourceStream, $this->getBodyStream());
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
        // normalize header names in case of 'Content-type' into 'Content-Type'
        $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));

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
     * @throws \AppserverIo\Http\HttpException Is thrown if the requested header is not available
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
     * Add's the cookie by name to the cookies array
     *
     * @param \AppserverIo\Http\CookieInterface $cookie The cookie object
     *
     * @return void
     */
    public function addCookie(CookieInterface $cookie)
    {
        // add's the cookie by name to the cookies array
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * Check if request has specific cookie
     *
     * @param string $name The name of the cookie to check for
     *
     * @return bool
     */
    public function hasCookie($name)
    {
        // check if request has specific cookie
        return (isset($this->cookies[$name]) &&  $this->cookies[$name]->getName() === $name);
    }

    /**
     * Get cookie by given name
     *
     * @param string $name The cookies name to get
     *
     * @return \AppserverIo\Http\HttpCookie|void
     */
    public function getCookie($name)
    {
        // check if has specific cookie
        if ($this->hasCookie($name)) {
            return $this->cookies[$name];
        }
    }

    /**
     * Just returns the array of cookie objects
     *
     * @return array
     */
    public function getCookies()
    {
        // just returns the array of cookie objects
        return $this->cookies;
    }

    /**
     * Resets the whole cookies array by another array collection of cookie instances
     *
     * @param array $cookies The array of Cookie instances
     *
     * @return void
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
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

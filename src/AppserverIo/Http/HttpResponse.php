<?php

/**
 * \AppserverIo\Http\HttpResponse
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

use AppserverIo\Psr\HttpMessage\Protocol;
use AppserverIo\Psr\HttpMessage\CookieInterface;
use AppserverIo\Psr\HttpMessage\ResponseInterface;

/**
 * Class HttpResponse
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
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
     * Sets the default response headers to response
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
     * Returns default headers array
     *
     * @return array
     */
    public function getDefaultHeaders()
    {
        return $this->defaultHeaders;
    }

    /**
     * Prepare's the headers for dispatch
     *
     * @return void
     */
    public function prepareHeaders()
    {
        // set current date before render it if no headers is set for yet
        if (!$this->hasHeader(HttpProtocol::HEADER_DATE)) {
            $this->addHeader(HttpProtocol::HEADER_DATE, gmdate(DATE_RFC822));
        }

        // check if no content length was set before
        if (!$this->hasHeader(HttpProtocol::HEADER_CONTENT_LENGTH)) {
            $inputStream = $this->getBodyStream();
            // try to get content length from stream resource if its seekable
            if (@fseek($inputStream, 0, SEEK_END) === 0) {
                $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, @ftell($inputStream));
                @rewind($inputStream);
            }
        }

        /**
         * Check if status code is content-length relevant and set it to be zero on existing one.
         * No content allowed for 1xx, 204 and 304.
         * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.3
         * @see https://tools.ietf.org/html/rfc2068#section-14.14
         */
        if ($this->hasHeader(HttpProtocol::HEADER_CONTENT_LENGTH)) {
            $statusCode = (int)$this->getStatusCode();
            if ($statusCode === 304 || $statusCode === 204 || ($statusCode >= 100 && $statusCode < 200)) {
                $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, 0);
            }
        }
    }

    /**
     * Returns all headers as string
     *
     * @return string
     */
    public function getHeaderString()
    {
        // initialize the string for the headers
        $headerString = '';

        // concatenate the headers to a string
        foreach ($this->getHeaders() as $headerName => $headerValue) {
            /**
             * Check if status code is content-length relevant and set it to be zero on existing one.
             * No content allowed for 1xx, 204 and 304.
             * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.3
             * @see https://tools.ietf.org/html/rfc2068#section-14.14
             */
            if ($headerName === HttpProtocol::HEADER_CONTENT_LENGTH) {
                $statusCode = (int)$this->getStatusCode();
                if ($statusCode === 304 || $statusCode === 204 || ($statusCode >= 100 && $statusCode < 200)) {
                    $this->addHeader(HttpProtocol::HEADER_CONTENT_LENGTH, 0);
                }
            }
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
        foreach ($this->getCookies() as $cookieName => $cookieValue) {
            // take care for multiple cookies
            if (is_array($cookieValue)) {
                // iterate of the cookies
                foreach ($cookieValue as $cookie) {
                    $headerString .= HttpProtocol::HEADER_SET_COOKIE . HttpProtocol::HEADER_SEPARATOR . $cookie->__toString() . "\r\n";
                }

            // if we've a single cookie, add it directly
            } elseif ($cookieValue instanceof HttpCookie) {
                $headerString .= HttpProtocol::HEADER_SET_COOKIE . HttpProtocol::HEADER_SEPARATOR . $cookieValue->__toString() . "\r\n";
            }
        }

        // return with ending CRLF
        return $headerString . "\r\n";
    }

    /**
     * ReSets the stream resource pointing to body content
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
     * Returns the stream resource pointing to body content
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
            // destroy it
            fclose($this->bodyStream);
        }
        // if nothing exists create a memory stream
        $this->bodyStream = fopen('php://memory', 'w+b');
    }

    /**
     * Returns the body content stored in body stream
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

            // add the header array
            $this->headers[$name] = $headerValue;

        } else {
            // when add it the first time, simply add it
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
        return isset($this->headers[$name]);
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
     * Returns all headers as array
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
     * Adds the cookie information got from connection. We've to take care that cookies can
     * multiple times. To support this create an array that keeps the multiple exist cookie
     * values.
     *
     * @param \AppserverIo\Psr\HttpMessage\CookieInterface $cookie The cookie object
     *
     * @return void
     */
    public function addCookie(CookieInterface $cookie)
    {

        // check if this cookie has already been set
        if ($this->hasCookie($name = $cookie->getName())) {
            // then check if we've already one cookie header available
            if (is_array($cookieValue = $this->getCookie($name))) {
                $cookieValue[] = $cookie;
            } else {
                $cookieValue = array($cookieValue, $cookie);
            }

            // add the cookie array
            $this->cookies[$name] = $cookieValue;

        } else {
            // when add it the first time, simply add it
            $this->cookies[$name] = $cookie;
        }
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
        return isset($this->cookies[$name]);
    }

    /**
     * Get cookie by given name
     *
     * @param string $name The cookies name to get
     *
     * @return mixed The cookie instance or an array of cookie instances
     * @throws \AppserverIo\Http\HttpException Is thrown if the cookie is not available
     */
    public function getCookie($name)
    {
        if ($this->hasCookie($name) === false) {
            throw new HttpException("Cookie '$name' not found");
        }
        return $this->cookies[$name];
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
     * Removes the cookie with the passed name.
     *
     * @param string $name Name of the cookie to remove
     *
     * @return void
     */
    public function removeCookie($name)
    {
        if (isset($this->cookies[$name])) {
            unset($this->cookies[$name]);
        }
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
     * Sets the http response status code
     *
     * @param int $code The status code to set
     *
     * @return void
     */
    public function setStatusCode($code)
    {
        // set status code
        $this->statusCode = $code;

        // we have to react on certain status codes in a certain way, do that here
        // We have to discard the body on status codes 1xx, 204 and 304.
        // @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.3
        // @see https://tools.ietf.org/html/rfc2068#section-14.14
        $statusCode = (int)$this->getStatusCode();
        if ($statusCode === 304 || $statusCode === 204 || ($statusCode >= 100 && $statusCode < 200)) {
            $this->resetBodyStream();
        }

        // lookup reason phrase by code and set
        $this->setStatusReasonPhrase(HttpProtocol::getStatusReasonPhraseByCode($code));
    }

    /**
     * Sets the status reason phrase
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
     * Returns the response status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets state of response
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
     * Returns the current state
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
     * Returns the status phrase based on the status code
     *
     * @return string
     */
    public function getStatusReasonPhrase()
    {
        return $this->statusReasonPhrase;
    }

    /**
     * Returns the http version of the response
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the http response version
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

    /**
     * Redirects to the passed URL by adding a 'Location' header and
     * setting the apropriate status code, by default 301.
     *
     * @param string  $url  The URL to forward to
     * @param integer $code The status code to set
     *
     * @return void
     */
    public function redirect($url, $code = 301)
    {
        $this->setStatusCode($code);
        $this->addHeader(HttpProtocol::HEADER_LOCATION, $url);
    }
}

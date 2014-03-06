<?php

/**
 * \TechDivision\Http\HttpProtocol
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
 * Class HttpProtocol
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 * @link      http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 */
class HttpProtocol
{

    /**
     * Status header name.
     *
     * @var string
     */
    const HEADER_STATUS = 'Status';
    
    /**
     * Date header name.
     *
     * @var string
     */
    const HEADER_DATE = 'Date';
    
    /**
     * Connection header name.
     *
     * @var string
     */
    const HEADER_CONNECTION = 'Connection';
    
    /**
     * Content-Type header name.
     *
     * @var string
     */
    const HEADER_CONTENT_TYPE = 'Content-Type';
    
    /**
     * Content-Length header name.
     *
     * @var string
     */
    const HEADER_CONTENT_LENGTH = 'Content-Length';
    
    /**
     * Content-Encoding header name.
     *
     * @var string
     */
    const HEADER_CONTENT_ENCODING = 'Content-Encoding';
    
    /**
     * Cache-Control header name.
     *
     * @var string
     */
    const HEADER_CACHE_CONTROL = 'Cache-Control';
    
    /**
     * Pragma header name.
     *
     * @var string
     */
    const HEADER_PRAGMA = 'Pragma';

    /**
     * Proxy-Connection header name
     *
     * @var string
     */
    const HEADER_PROXY_CONNECTION = 'Proxy-Connection';

    /**
     * X-Forward header name
     *
     * @var string
     */
    const HEADER_X_FORWARD = 'X-Forward';
    
    /**
     * Status header name.
     *
     * @var string
     */
    const HEADER_LAST_MODIFIED = 'Last-Modified';
    
    /**
     * Expires header name.
     *
     * @var string
     */
    const HEADER_EXPIRES = 'Expires';
    
    /**
     * If-Modified-Since header name.
     *
     * @var string
     */
    const HEADER_IF_MODIFIED_SINCE = 'If-Modified-Since';
    
    /**
     * Location header name.
     *
     * @var string
     */
    const HEADER_LOCATION = 'Location';
    
    /**
     * X-Powered-By header name.
     *
     * @var string
     */
    const HEADER_X_POWERED_BY = 'X-Powered-By';
    
    /**
     * Cookie header name.
     *
     * @var string
     */
    const HEADER_COOKIE = 'Cookie';
    
    /**
     * Set-Cookie header name.
     *
     * @var string
     */
    const HEADER_SET_COOKIE = 'Set-Cookie';
    
    /**
     * Host header name.
     *
     * @var string
     */
    const HEADER_HOST = 'Host';
    
    /**
     * Accept header name.
     *
     * @var string
     */
    const HEADER_ACCEPT = 'Accept';

    /**
     * Accept-Charset header name
     *
     * @var string
     */
    const HEADER_ACCEPT_CHARSET = 'Accept-Charset';

    /**
     * Accept-Language header name.
     *
     * @var string
     */
    const HEADER_ACCEPT_LANGUAGE = 'Accept-Language';
    
    /**
     * Accept-Encoding header name.
     *
     * @var string
     */
    const HEADER_ACCEPT_ENCODING = 'Accept-Encoding';
    
    /**
     * User-Agent header name.
     *
     * @var string
     */
    const HEADER_USER_AGENT = 'User-Agent';
    
    /**
     * Referer header name.
     *
     * @var string
     */
    const HEADER_REFERER = 'Referer';
    
    /**
     * Keep-Alive header name.
     *
     * @var string
     */
    const HEADER_KEEP_ALIVE = 'Keep-Alive';
    
    /**
     * Server header name.
     *
     * @var string
     */
    const HEADER_SERVER = 'Server';
    
    /**
     * WWW-Authenticate header name.
     *
     * @var string
     */
    const HEADER_WWW_AUTHENTICATE = 'WWW-Authenticate';
    
    /**
     * Authorization header name.
     *
     * @var string
     */
    const HEADER_AUTHORIZATION = 'Authorization';

    /**
     * Mainly used to identify Ajax requests.
     * Most JavaScript frameworks send this header with value of XMLHttpRequest
     *
     * @var string
     */
    const HEADER_X_REQUESTED_WITH = 'X-Requested-With';

    /**
     * POST request method string.
     *
     * @var string
     */
    const METHOD_POST = 'POST';
    
    /**
     * GET request method string.
     *
     * @var string
     */
    const METHOD_GET = 'GET';
    
    /**
     * HEAD request method string.
     *
     * @var string
     */
    const METHOD_HEAD = 'HEAD';
    
    /**
     * PUT request method string.
     *
     * @var string
     */
    const METHOD_PUT = 'PUT';
    
    /**
     * DELETE request method string.
     *
     * @var string
     */
    const METHOD_DELETE = 'DELETE';
    
    /**
     * OPTIONS request method string.
     *
     * @var string
     */
    const METHOD_OPTIONS = 'OPTIONS';
    
    /**
     * TRACE request method string.
     *
     * @var string
     */
    const METHOD_TRACE = 'TRACE';
    
    /**
     * CONNECT request method string.
     *
     * @var string
     */
    const METHOD_CONNECT = 'CONNECT';
    
    /**
     * Defines status const's
     *
     * @var string
     */
    const STATUS_REASONPHRASE_UNASSIGNED = 'Unassigned';

    /**
     * Defines reasonPhrases array
     *
     * @var array
     */
    static public $reasonPhrases = array(
        100 => "Continue", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.2.1]
        101 => "Switching Protocols", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.2.2]
        102 => "Processing", //[RFC2518]
        200 => "OK", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.3.1]
        201 => "Created", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.3.2]
        202 => "Accepted", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.3.3]
        203 => "Non-Authoritative Information", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.3.4]
        204 => "No Content", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.3.5]
        205 => "Reset Content", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.3.6]
        206 => "Partial Content", //[RFC-ietf-httpbis-p5-range-26, Section 4.1]
        207 => "Multi-Status", //[RFC4918]
        208 => "Already Reported", //[RFC5842]
        226 => "IM Used", //[RFC3229]
        300 => "Multiple Choices", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.1]
        301 => "Moved Permanently", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.2]
        302 => "Found", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.3]
        303 => "See Other", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.4]
        304 => "Not Modified", //[RFC-ietf-httpbis-p4-conditional-26, Section 4.1]
        305 => "Use Proxy", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.5]
        306 => "(Unused)", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.6]
        307 => "Temporary Redirect", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.4.7]
        308 => "Permanent Redirect", //[RFC-reschke-http-status-308-07]
        400 => "Bad Request", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.1]
        401 => "Unauthorized", //[RFC-ietf-httpbis-p7-auth-26, Section 3.1]
        402 => "Payment Required", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.2]
        403 => "Forbidden", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.3]
        404 => "Not Found", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.4]
        405 => "Method Not Allowed", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.5]
        406 => "Not Acceptable", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.6]
        407 => "Proxy Authentication Required", //[RFC-ietf-httpbis-p7-auth-26, Section 3.2]
        408 => "Request Timeout", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.7]
        409 => "Conflict", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.8]
        410 => "Gone", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.9]
        411 => "Length Required", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.10]
        412 => "Precondition Failed", //[RFC-ietf-httpbis-p4-conditional-26, Section 4.2]
        413 => "Payload Too Large", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.11]
        414 => "URI Too Long", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.12]
        415 => "Unsupported Media Type", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.13]
        416 => "Requested Range Not Satisfiable", //[RFC-ietf-httpbis-p5-range-26, Section 4.4]
        417 => "Expectation Failed", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.14]
        422 => "Unprocessable Entity", //[RFC4918]
        423 => "Locked", //[RFC4918]
        424 => "Failed Dependency", //[RFC4918]
        426 => "Upgrade Required", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.5.15]
        428 => "Precondition Required", //[RFC6585]
        429 => "Too Many Requests", //[RFC6585]
        431 => "Request Header Fields Too Large", //[RFC6585]
        500 => "Internal Server Error", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.6.1]
        501 => "Not Implemented", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.6.2]
        502 => "Bad Gateway", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.6.3]
        503 => "Service Unavailable", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.6.4]
        504 => "Gateway Timeout", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.6.5]
        505 => "HTTP Version Not Supported", //[RFC-ietf-httpbis-p2-semantics-26, Section 6.6.6]
        506 => "Variant Also Negotiates (Experimental)", //[RFC2295]
        507 => "Insufficient Storage", //[RFC4918]
        508 => "Loop Detected", //[RFC5842]
        510 => "Not Extended", //[RFC2774]
        511 => "Network Authentication Required", //[RFC6585]
    );

    /**
     * Return's the reason phrase by given status code
     *
     * @param int $statusCode The http status code
     *
     * @return string The reason phrase
     */
    public static function getStatusReasonPhraseByCode($statusCode)
    {
        if (array_key_exists($statusCode, self::$reasonPhrases)) {
            return self::$reasonPhrases[$statusCode];
        }
        return self::STATUS_REASONPHRASE_UNASSIGNED;
    }
}

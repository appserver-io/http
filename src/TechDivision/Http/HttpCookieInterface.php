<?php

/**
 * TechDivision\Http\HttpCookieInterface
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
 * Represents an interface for HTTP Cookies as of RFC 6265
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 * @see       http://tools.ietf.org/html/rfc6265
 */
interface HttpCookieInterface
{

    /**
     * Returns the name of this cookie
     *
     * @return string The cookie name
     * @api
     */
    public function getName();

    /**
     * Returns the value of this cookie
     *
     * @return mixed
     * @api
     */
    public function getValue();

    /**
     * Returns the date and time of the Expires attribute, if any.
     *
     * Note that this date / time is returned as a unix timestamp, no matter what
     * the format was originally set through the constructor of this Cookie.
     *
     * The special case "no expiration time" is returned in form of a zero value.
     *
     * @return integer A unix timestamp or 0
     * @api
     */
    public function getExpires();

    /**
     * Returns the number of seconds until the cookie expires, if defined.
     *
     * This information is rendered as the Max-Age attribute (RFC 6265, 4.1.2.2).
     * Note that not all browsers support this attribute.
     *
     * @return integer The maximum age in seconds, or NULL if none has been defined.
     * @api
     */
    public function getMaximumAge();

    /**
     * Returns the domain this cookie is valid for.
     *
     * @return string The domain name
     * @api
     */
    public function getDomain();

    /**
     * Returns the path this cookie is valid for.
     *
     * @return string The path
     * @api
     */
    public function getPath();

    /**
     * Tells if the cookie was flagged to be sent over "secure" channels only.
     *
     * This security measure only has a limited effect. Please read RFC 6265 Section 8.6
     * for more details.
     *
     * @return boolean State of the "Secure" attribute
     * @api
     */
    public function isSecure();

    /**
     * Tells if this cookie should only be used through the HTTP protocol.
     *
     * @return boolean State of the "HttpOnly" attribute
     * @api
     */
    public function isHttpOnly();

    /**
     * Marks this cookie for removal.
     *
     * On executing this method, the expiry time of this cookie is set to a point
     * in time in the past triggers the removal of the cookie in the user agent.
     *
     * @return void
     */
    public function expire();

    /**
     * Tells if this cookie is expired and will be removed in the user agent when it
     * received the response containing this cookie.
     *
     * @return boolean True if this cookie will is expired
     */
    public function isExpired();

    /**
     * Renders the field value suitable for a HTTP "Set-Cookie" header.
     *
     * @return string
     */
    public function __toString();
}

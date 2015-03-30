<?php

/**
 * \AppserverIo\Http\HttpCookie
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */

namespace AppserverIo\Http;

/*
 * This script belongs to the TYPO3 Flow framework.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License, either version 3
 * of the License, or (at your option) any later version.
 *
 * The TYPO3 project - inspiring people to share!
 */

use AppserverIo\Psr\HttpMessage\CookieInterface;

/**
 * Represents a HTTP Cookie as of RFC 6265
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 * @see       http://tools.ietf.org/html/rfc6265
 */
class HttpCookie implements CookieInterface
{

    /**
     * Domain name for 'localhost'
     *
     * @var string
     */
    const LOCALHOST = 'localhost';

    /**
     * A token as per RFC 2616, Section 2.2
     */
    const PATTERN_TOKEN = '/^([\x21\x23-\x27\x2A-\x2E0-9A-Z\x5E-\x60a-z\x7C\x7E]+)$/';

    /**
     * The max age pattern as per RFC 6265, Section 5.2.2
     */
    const PATTERN_MAX_AGE = '/^\-?\d+$/';

    /**
     * A simplified pattern for a basically valid domain (<subdomain>) as per RFC 6265, 4.1.1 / RFC 1034, 3.5 + RFC 1123, 2.1
     */
    const PATTERN_DOMAIN = '/^([a-z0-9]+[a-z0-9.-]*[a-z0-9])$|([0-9\.]+[0-9])$/i';

    /**
     * A path as per RFC 6265, 4.1.1
     */
    const PATTERN_PATH = '/^([\x20-\x3A\x3C-\x7E])+$/';

    /**
     * Cookie Name, a token (RFC 6265, 4.1.1)
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $value;

    /**
     * Unix timestamp of the expiration date / time or 0 for "session" expiration (RFC 6265, 4.1.2.1)
     *
     * @var integer
     */
    protected $expiresTimestamp;

    /**
     * Number of seconds until the cookie expires (RFC 6265, 4.1.2.2)
     *
     * @var integer
     */
    protected $maximumAge;

    /**
     * Hosts to which this cookie will be sent (RFC 6265, 4.1.2.3)
     *
     * @var string
     */
    protected $domain;

    /**
     *
     * @var string
     */
    protected $path;

    /**
     *
     * @var boolean
     */
    protected $secure;

    /**
     *
     * @var boolean
     */
    protected $httpOnly;

    /**
     * Constructs a new HttpCookie object
     *
     * @param string            $name       The cookie name as a valid token (RFC 2616)
     * @param mixed             $value      The value to store in the cookie. Must be possible to cast into a string.
     * @param integer|\DateTime $expires    Date and time after which this cookie expires.
     * @param integer|null      $maximumAge Number of seconds until the cookie expires.
     * @param string|null       $domain     The host to which the user agent will send this cookie
     * @param string            $path       The path describing the scope of this cookie
     * @param boolean           $secure     If this cookie should only be sent through a "secure" channel by the user agent
     * @param boolean           $httpOnly   If this cookie should only be used through the HTTP protocol
     */
    public function __construct($name, $value = null, $expires = 0, $maximumAge = null, $domain = null, $path = '/', $secure = false, $httpOnly = true)
    {

        // check if valid data is passed
        if (preg_match(self::PATTERN_TOKEN, $name) !== 1) {
            throw new \InvalidArgumentException('The parameter "name" passed to the Cookie constructor must be a valid token as per RFC 2616, Section 2.2.', 1345101977);
        }
        if ($expires instanceof \Datetime) {
            $expires = $expires->getTimestamp();
        }
        if (! is_integer($expires)) {
            throw new \InvalidArgumentException('The parameter "expires" passed to the Cookie constructor must be a unix timestamp or a DateTime object.', 1345108785);
        }
        if ($maximumAge !== null && ! is_integer($maximumAge)) {
            throw new \InvalidArgumentException('The parameter "maximumAge" passed to the Cookie constructor must be an integer value.', 1345108786);
        }
        if ($domain !== null && preg_match(self::PATTERN_DOMAIN, $domain) !== 1) {
            throw new \InvalidArgumentException('The parameter "domain" passed to the Cookie constructor must be a valid domain as per RFC 6265, Section 4.1.2.3.', 1345116246);
        }
        if ($path !== null && preg_match(self::PATTERN_PATH, $path) !== 1) {
            throw new \InvalidArgumentException('The parameter "path" passed to the Cookie constructor must be a valid path as per RFC 6265, Section 4.1.1.', 1345123078);
        }

        /*
         * If the domain is 'localhost' do NOT set it
         *
         * Fix for Chrome issue https://code.google.com/p/chromium/issues/detail?id=56211
         */
        if ($domain !== HttpCookie::LOCALHOST) {
            $this->domain = $domain;
        }

        // set the other cookie values
        $this->name = $name;
        $this->value = $value;
        $this->expiresTimestamp = $expires;
        $this->maximumAge = $maximumAge;
        $this->path = $path;
        $this->secure = ($secure == true);
        $this->httpOnly = ($httpOnly == true);
    }

    /**
     * Creates a cookie (an instance of this class) by a provided
     * raw header string like "foo=507d9f20317a5; path=/; domain=.example.org"
     * This is is an implementation of the algorithm explained in RFC 6265, Section 5.2
     * A basic statement of this algorithm is to "ignore the set-cookie-string entirely"
     * in case a required condition is not met. In these cases this function will return NULL
     * rather than the created cookie.
     *
     * @param string $header The Set-Cookie string without the actual "Set-Cookie:" part
     *
     * @return \AppserverIo\Http\HttpCookie
     * @see http://tools.ietf.org/html/rfc6265
     */
    public static function createFromRawSetCookieHeader($header)
    {
        $nameValueAndUnparsedAttributes = explode(';', $header, 2);
        $expectedNameValuePair = $nameValueAndUnparsedAttributes[0];
        $unparsedAttributes = isset($nameValueAndUnparsedAttributes[1]) ? $nameValueAndUnparsedAttributes[1] : '';

        if (strpos($expectedNameValuePair, '=') === false) {
            return null;
        }
        $cookieNameAndValue = explode('=', $expectedNameValuePair, 2);
        $cookieName = trim($cookieNameAndValue[0]);
        $cookieValue = isset($cookieNameAndValue[1]) ? trim($cookieNameAndValue[1]) : '';
        if ($cookieName === '') {
            return null;
        }

        $expiresAttribute = 0;
        $maxAgeAttribute = null;
        $domainAttribute = null;
        $pathAttribute = null;
        $secureAttribute = false;
        $httpOnlyAttribute = true;

        if ($unparsedAttributes !== '') {
            foreach (explode(';', $unparsedAttributes) as $cookieAttributeValueString) {
                $attributeNameAndValue = explode('=', $cookieAttributeValueString, 2);
                $attributeName = trim($attributeNameAndValue[0]);
                $attributeValue = isset($attributeNameAndValue[1]) ? trim($attributeNameAndValue[1]) : '';
                switch (strtoupper($attributeName)) {
                    case 'EXPIRES':
                        try {
                            $expiresAttribute = new \DateTime($attributeValue);
                        } catch (\Exception $exception) {
                            // as of RFC 6265 Section 5.2.1, a non parsable Expires date should result into
                            // ignoring, but since the Cookie constructor relies on it, we'll
                            // assume a Session cookie with an expiry date of 0.
                            $expiresAttribute = 0;
                        }
                        break;
                    case 'MAX-AGE':
                        if (preg_match(self::PATTERN_MAX_AGE, $attributeValue) === 1) {
                            $maxAgeAttribute = intval($attributeValue);
                        }
                        break;
                    case 'DOMAIN':
                        if ($attributeValue !== '') {
                            $domainAttribute = strtolower(ltrim($attributeValue, '.'));
                        }
                        break;
                    case 'PATH':
                        if ($attributeValue === '' || substr($attributeValue, 0, 1) !== '/') {
                            $pathAttribute = '/';
                        } else {
                            $pathAttribute = $attributeValue;
                        }
                        break;
                    case 'SECURE':
                        $secureAttribute = true;
                        break;
                    case 'HTTPONLY':
                        $httpOnlyAttribute = true;
                        break;
                }
            }
        }

        $cookie = new HttpCookie($cookieName, $cookieValue, $expiresAttribute, $maxAgeAttribute, $domainAttribute, $pathAttribute, $secureAttribute, $httpOnlyAttribute);

        return $cookie;
    }

    /**
     * Returns the name of this cookie
     *
     * @return string The cookie name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of this cookie
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of this cookie
     *
     * @param mixed $value The new value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the date and time of the Expires attribute, if any.
     *
     * Note that this date / time is returned as a unix timestamp, no matter what
     * the format was originally set through the constructor of this Cookie.
     *
     * The special case "no expiration time" is returned in form of a zero value.
     *
     * @return integer A unix timestamp or 0
     */
    public function getExpires()
    {
        return $this->expiresTimestamp;
    }

    /**
     * Returns the number of seconds until the cookie expires, if defined.
     *
     * This information is rendered as the Max-Age attribute (RFC 6265, 4.1.2.2).
     * Note that not all browsers support this attribute.
     *
     * @return integer The maximum age in seconds, or NULL if none has been defined.
     */
    public function getMaximumAge()
    {
        return $this->maximumAge;
    }

    /**
     * Returns the domain this cookie is valid for.
     *
     * @return string The domain name
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Returns the path this cookie is valid for.
     *
     * @return string The path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Tells if the cookie was flagged to be sent over "secure" channels only.
     *
     * This security measure only has a limited effect. Please read RFC 6265 Section 8.6
     * for more details.
     *
     * @return boolean State of the "Secure" attribute
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Tells if this cookie should only be used through the HTTP protocol.
     *
     * @return boolean State of the "HttpOnly" attribute
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Marks this cookie for removal.
     *
     * On executing this method, the expiry time of this cookie is set to a point
     * in time in the past triggers the removal of the cookie in the user agent.
     *
     * @return void
     */
    public function expire()
    {
        $this->expiresTimestamp = 202046400;
    }

    /**
     * Tells if this cookie is expired and will be removed in the user agent when it
     * received the response containing this cookie.
     *
     * @return boolean True if this cookie will is expired
     */
    public function isExpired()
    {
        return ($this->expiresTimestamp !== 0 && $this->expiresTimestamp < time());
    }

    /**
     * Renders the field value suitable for a HTTP "Set-Cookie" header.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->value === false) {
            $value = 0;
        } else {
            $value = $this->value;
        }

        $cookiePair = sprintf('%s=%s', $this->name, urlencode($value));
        $attributes = '';

        if ($this->expiresTimestamp !== 0) {
            $attributes .= '; Expires=' . gmdate('D, d-M-Y H:i:s T', $this->expiresTimestamp);
        }

        if ($this->domain !== null) {
            $attributes .= '; Domain=' . $this->domain;
        }

        $attributes .= '; Path=' . $this->path;

        if ($this->secure) {
            $attributes .= '; Secure';
        }

        if ($this->httpOnly) {
            $attributes .= '; HttpOnly';
        }

        return $cookiePair . $attributes;
    }
}

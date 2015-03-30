<?php
/**
 * \AppserverIo\Http\CookieTest
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

/**
 * Class RequestParserTest
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */
class CookieTest extends \PHPUnit_Framework_TestCase {

    /**
     * Initializes parser object to test.
     *
     * @return void
     */
    public function setUp() {
        // nothing to do yet
    }

    /**
     * Test instantiation of cookie object class
     */
    public function testHttpCookieInstantiationOfCookieObject()
    {
        $hash = md5(time());
        $dateTimeNow = new \DateTime();
        // init parser
        $cookie = new HttpCookie(
            'testCookieName001',
            $hash,
            $dateTimeNow,
            null,
            'testdomain.local',
            '/',
            false,
            true
        );

        // assert stuff
        $this->assertSame($cookie->getName(), 'testCookieName001');
        $this->assertSame($cookie->getValue(), $hash);
        $this->assertSame($cookie->getExpires(), $dateTimeNow->getTimestamp());
        $this->assertSame($cookie->getMaximumAge(), null);
        $this->assertSame($cookie->getDomain(), 'testdomain.local');
        $this->assertSame($cookie->getPath(), '/');
        $this->assertSame($cookie->isSecure(), false);
        $this->assertSame($cookie->isHttpOnly(), true);
    }

    /**
     * Test if is expired on a expired cookie
     */
    public function testHttpCookieIsExpiredOnAExpiredCookie()
    {
        $hash = md5(time());
        $dateTime = new \DateTime();
        // let it expire to be 1 second in the past
        $dateTime->modify("-1 second");
        // init parser
        $cookie = new HttpCookie(
            'testCookieName001',
            $hash,
            $dateTime,
            null,
            'testdomain.local',
            '/',
            false,
            true
        );
        $this->assertSame($cookie->isExpired(), true);
    }

    /**
     * Test if is expired on a non expired cookie
     */
    public function testHttpCookieIsExpiredOnANonExpiredCookie()
    {
        $hash = md5(time());
        $dateTime = new \DateTime();
        // let it expire to be 1 second in the future
        $dateTime->modify("+1 second");
        // init parser
        $cookie = new HttpCookie(
            'testCookieName001',
            $hash,
            $dateTime,
            null,
            'testdomain.local',
            '/',
            false,
            true
        );
        $this->assertSame($cookie->isExpired(), false);
    }

    /**
     * Test explicit expire on a non expired cookie
     */
    public function testHttpCookieExplicitExpireOnANonExpiredCookie()
    {
        $hash = md5(time());
        $dateTime = new \DateTime();
        // let it expire to be 1 hour in the future
        $dateTime->modify("+1 hour");
        // init parser
        $cookie = new HttpCookie(
            'testCookieName001',
            $hash,
            $dateTime,
            null,
            'testdomain.local',
            '/',
            false,
            true
        );
        // should not be expired
        $this->assertSame($cookie->isExpired(), false);
        // let the cookie expire
        $cookie->expire();
        // now it should be expired
        $this->assertSame($cookie->isExpired(), true);
    }

    /**
     * Test the __toString method on a full filled cookie object
     */
    public function testHttpCookieCookieToStringMethodWithAllParamsSet()
    {
        $dateTime = new \DateTime('2014-07-02 01:02:03 GMT');
        // init parser
        $cookie = new HttpCookie(
            'testCookieName001',
            'bfcb71b48c723ff3b8a02eaa5f08ce7b',
            $dateTime,
            null,
            'testdomain.local',
            '/',
            false,
            true
        );
        $this->assertSame($cookie->__toString(), 'testCookieName001=bfcb71b48c723ff3b8a02eaa5f08ce7b; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain.local; Path=/; HttpOnly');
    }

    /**
     * Test the __toString method on a partial filled cookie object
     */
    public function testHttpCookieCookieToStringMethodWithPartialParamsSet()
    {
        $dateTime = new \DateTime('2014-07-02 01:02:03 GMT');
        // init parser
        $cookie = new HttpCookie(
            'testCookieName001',
            'bfcb71b48c723ff3b8a02eaa5f08ce7b'
        );
        $this->assertSame($cookie->__toString(), 'testCookieName001=bfcb71b48c723ff3b8a02eaa5f08ce7b; Path=/; HttpOnly');
    }

    /**
     * Test create From Raw Cookie Request Header
     */
    public function testHttpCookieCreateFromRawCookieRequestHeader()
    {
        $cookieHeader = 'cookietestname01=2q9nfp98q2funq423iuf;  ';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        // check name and value to be parsed correctly
        $this->assertSame($cookie->getName(), 'cookietestname01');
        $this->assertSame($cookie->getValue(), '2q9nfp98q2funq423iuf');
    }

    /**
     * Test create From Raw Set Cookie Response Header
     */
    public function testHttpCookieCreateFromRawSetCookieResponseHeader()
    {
        $cookieHeader = 'UserID=FooBar; Max-Age=3600; Version=1; Domain=test.local; Path=/testpath';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        // check properties to be correct
        $this->assertSame($cookie->getName(), 'UserID');
        $this->assertSame($cookie->getValue(), 'FooBar');
        $this->assertSame($cookie->getExpires(), 0);
        $this->assertSame($cookie->getMaximumAge(), 3600);
        $this->assertSame($cookie->getDomain(), 'test.local');
        $this->assertSame($cookie->getPath(), '/testpath');
        $this->assertSame($cookie->isSecure(), false);
        $this->assertSame($cookie->isHttpOnly(), true);
    }

    public function testHttpCookieCreateFromRawSetCookieResponseHeaderWithUnexpectedNameValuePair()
    {
        $cookieHeader = 'UserID; Max-Age=3600; Version=1; Domain=test.local; Path=/testpath';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        $this->assertNull($cookie);
    }

    public function testHttpCookieCreateFromRawSetCookieResponseHeaderWithoutCookieName()
    {
        $cookieHeader = '=value; Max-Age=3600; Version=1; Domain=test.local; Path=/testpath';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        $this->assertNull($cookie);
    }

    public function testHttpCookieCreateFromRawSetCookieResponseHeaderWithInvalidExpiresTimestamp()
    {
        $cookieHeader = 'cookieName=value; Expires=asdfasdf; Version=1; Domain=test.local; Path=/testpath';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        $this->assertSame(0, $cookie->getExpires());
    }

    public function testHttpCookieCreateFromRawSetCookieResponseHeaderWithEmptyPath()
    {
        $cookieHeader = 'cookieName=value; Expires=asdfasdf; Version=1; Domain=test.local; Path=';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        $this->assertSame('/', $cookie->getPath());
    }

    public function testHttpCookieCreateFromRawSetCookieResponseHeaderWithSecureEnabled()
    {
        $cookieHeader = 'cookieName=value; Expires=asdfasdf; Version=1; Domain=test.local; Path=/; Secure;';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        $this->assertSame(true, $cookie->isSecure());
    }

    public function testHttpCookieCreateFromRawSetCookieResponseHeaderWithHttpOnlyEnabled()
    {
        $cookieHeader = 'cookieName=value; Expires=asdfasdf; Version=1; Domain=test.local; Path=/; Secure; HttpOnly';
        // init cookie by raw header
        $cookie = HttpCookie::createFromRawSetCookieHeader($cookieHeader);
        $this->assertSame(true, $cookie->isHttpOnly());
    }

    public function testHttpCookieConstructorWithInvalidNameArgument()
    {
        $testException = null;
        try {
            $cookie = new HttpCookie('#testCookie\\/', md5(1), 0, null, 'testdomain.local', '/', false, true);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    public function testHttpCookieConstructorWithInvalidExpireArgument()
    {
        $testException = null;
        try {
            $cookie = new HttpCookie('testCookie', md5(1), '12738718273', null, 'testdomain.local', '/', false, true);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    public function testHttpCookieConstructorWithInvalidMaximumAgeArgument()
    {
        $testException = null;
        try {
            $cookie = new HttpCookie('testCookie', md5(1), 0, '12', 'testdomain.local', '/', false, true);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    public function testHttpCookieConstructorWithInvalidDomainArgument()
    {
        $testException = null;
        try {
            $cookie = new HttpCookie('testCookie', md5(1), 0, null, 'testdomain-#\\', '/', false, true);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    public function testHttpCookieConstructorWithInvalidPathArgument()
    {
        $testException = null;
        try {
            $cookie = new HttpCookie('testCookie', md5(1), 0, null, 'testdomain.local', '§/§/§/§/', false, true);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    public function testHttpCookieSetAndGetValueMethod()
    {
        $cookie = new HttpCookie('testCookie');
        $testValue = md5(time());
        $cookie->setValue($testValue);
        $this->assertSame($testValue, $cookie->getValue());
    }

    public function testHttpCookieToStringMethodWithBooleanFalseAsValue()
    {
        $cookie = new HttpCookie('testCookie');
        $cookie->setValue(false);
        $this->assertSame($cookie->__toString(), 'testCookie=0; Path=/; HttpOnly');
    }
}

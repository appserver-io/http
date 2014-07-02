<?php
/**
 * \TechDivision\Http\CookieTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Library
 * @package    TechDivision_Http
 * @subpackage tests
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_Http
 */

namespace TechDivision\Http;

use TechDivision\Http\HttpCookie;

/**
 * Class RequestParserTest
 *
 * @category   Library
 * @package    TechDivision_Http
 * @subpackage tests
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_Http
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
    public function testInstantiationOfCookieObject()
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
    public function testIsExpiredOnAExpiredCookie()
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
    public function testIsExpiredOnANonExpiredCookie()
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
    public function testExplicitExpireOnANonExpiredCookie()
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
    public function testCookieToStringMethodWithAllParamsSet()
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
    public function testCookieToStringMethodWithPartialParamsSet()
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
    public function testCreateFromRawCookieRequestHeader()
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
    public function testCreateFromRawSetCookieResponseHeader()
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
}

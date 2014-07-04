<?php
/**
 * \TechDivision\Http\HttpResponseTest
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

/**
 * Class HttpResponseTest
 *
 * @category   Library
 * @package    TechDivision_Http
 * @subpackage tests
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_Http
 */
class ResponseTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var HttpResponse
     */
    public $response;

    /**
     * Initializes response object to test.
     *
     * @return void
     */
    public function setUp() {
        $this->response = new HttpResponse();
        $this->response->init();
    }

    /**
     * Test add header functionality on response object.
     */
    public function testAddHeaderToResponseObject() {
        $contentLength = rand(0,100000);
        $this->response->addHeader(HttpProtocol::HEADER_X_POWERED_BY, 'PhpUnit');
        $this->response->addHeader(HttpProtocol::HEADER_CONTENT_TYPE, $contentLength);

        $this->assertSame('PhpUnit', $this->response->getHeader(HttpProtocol::HEADER_X_POWERED_BY));
        $this->assertSame($contentLength, $this->response->getHeader(HttpProtocol::HEADER_CONTENT_TYPE));
    }

    /**
     * Test set status code on response object and get correct status line.
     */
    public function testSetStatusCodeToResponseObjectAndGetStatusLine()
    {
        $this->response->setStatusCode(200);
        $this->assertSame("HTTP/1.1 200 OK\r\n", $this->response->getStatusLine());
        $this->response->setStatusCode(404);
        $this->assertSame("HTTP/1.1 404 Not Found\r\n", $this->response->getStatusLine());
        $this->response->setStatusCode(500);
        $this->assertSame("HTTP/1.1 500 Internal Server Error\r\n", $this->response->getStatusLine());
    }

    /**
     * Test add one cookie and get correct header string
     */
    public function testAddOneCookieToResponseObjectAndGetHeaderString()
    {
        // added date time
        $dateTime = new \DateTime('2014-07-02 01:02:03 GMT');
        // init cookie
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
        // add cookie to response
        $this->response->addCookie($cookie);
        // get header string
        $headerString = $this->response->getHeaderString();

        // check header string
        $this->assertSame($headerString, "Set-Cookie: testCookieName001=bfcb71b48c723ff3b8a02eaa5f08ce7b; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain.local; Path=/; HttpOnly\r\n\r\n");
    }

    /**
     * Test add many cookie and get correct header string
     */
    public function testAddManyCookiesToResponseObjectAndGetHeaderString()
    {
        // added date time
        $dateTime = new \DateTime('2014-07-02 01:02:03 GMT');
        // iterate and check values
        for ($i = 1; $i <= 5; $i++) {
            $this->response->addCookie(
                new HttpCookie(
                    "testCookieName00$i",
                    md5($i),
                    $dateTime,
                    null,
                    "testdomain00$i.local",
                    "/path$i$i$i",
                    $i === 3,
                    $i !== 1
                )
            );
        }
        // get header string
        $headerString = $this->response->getHeaderString();

        $this->assertSame($headerString,
            "Set-Cookie: testCookieName001=c4ca4238a0b923820dcc509a6f75849b; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain001.local; Path=/path111\r\n" .
            "Set-Cookie: testCookieName002=c81e728d9d4c2f636f067f89cc14862c; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain002.local; Path=/path222; HttpOnly\r\n" .
            "Set-Cookie: testCookieName003=eccbc87e4b5ce2fe28308fd9f2a7baf3; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain003.local; Path=/path333; Secure; HttpOnly\r\n" .
            "Set-Cookie: testCookieName004=a87ff679a2f3e71d9181a67b7542122c; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain004.local; Path=/path444; HttpOnly\r\n" .
            "Set-Cookie: testCookieName005=e4da3b7fbbce2345d7772b0674a318d5; Expires=Wed, 02-Jul-2014 01:02:03 GMT; Domain=testdomain005.local; Path=/path555; HttpOnly\r\n\r\n"
        );
    }
}

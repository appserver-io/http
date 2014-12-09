<?php
/**
 * \AppserverIo\Http\HttpResponseTest
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

/**
 * Class HttpResponseTest
 *
 * @category  Library
 * @package   Http
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2014 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
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
    }

    /**
     * Test's if http response body stream is a resource after calling init
     * @see https://github.com/appserver-io/http/issues/79
     */
    public function testInitBodyStreamToBeAResource()
    {
        $response = $this->response;
        $this->assertSame(true, is_resource($response->getBodyStream()));
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

    /**
     * Test's the status line getter with a non existing http status code
     */
    public function testGetResponseStatusLineWithNonExistingHttpStatusCode()
    {
        $this->response->setStatusCode(999);
        $this->assertSame("HTTP/1.1 999 Unassigned\r\n", $this->response->getStatusLine());
    }

    /**
     * Test's set and get of default headers
     */
    public function testSetAndGetDefaultHeaders()
    {
        $response = $this->response;
        $defaultHeaders = array(
            'Content-Type' => 'text/plain',
            'Content-Length' => '23523'
        );
        $response->setDefaultHeaders($defaultHeaders);
        $this->assertSame($defaultHeaders, $response->getDefaultHeaders());
    }

    /**
     * Test's get content length with empty body stream
     */
    public function testGetContentLengthWithEmptyBodyStream()
    {
        $response = $this->response;
        $this->assertSame(0, $response->getContentLength());
    }

    /**
     * Test's get content length with filled body stream
     */
    public function testGetContentLengthWithFilledBodyStream()
    {
        $response = $this->response;
        // generate random string
        for ($s = '', $cl = strlen($c = 'abcdefghijklmnopqrstuvwxyz1234567890')-1, $i = 0; $i < mt_rand(500,2000); $s .= $c[mt_rand(0, $cl)], ++$i);
        $response->appendBodyStream($s);
        $this->assertSame(strlen($s), $response->getContentLength());
    }

    /**
     * Test's get content length with response code between 300 and 399
     */
    public function testGetContentLengthWithResponseCodeBetween300And399()
    {
        $response = $this->response;
        // generate random string
        for ($s = '', $cl = strlen($c = 'abcdefghijklmnopqrstuvwxyz1234567890')-1, $i = 0; $i < mt_rand(500,2000); $s .= $c[mt_rand(0, $cl)], ++$i);
        $response->appendBodyStream($s);
        $response->setStatusCode(302);
        $this->assertSame(0, $response->getContentLength());
    }

    /**
     * Test's the preparation of headers
     */
    public function testPreparationOfHeaders()
    {
        $response = $this->response;
        // generate random string
        for ($s = '', $cl = strlen($c = 'abcdefghijklmnopqrstuvwxyz1234567890')-1, $i = 0; $i < mt_rand(500,2000); $s .= $c[mt_rand(0, $cl)], ++$i);
        $response->appendBodyStream($s);
        $response->prepareHeaders();
        $this->assertSame(array(
            'Date' => $response->getHeader('Date'),
            'Content-Length' => strlen($s)
        ), $response->getHeaders());
    }

    public function testGetHeaderStringWithManuallyAddedAppendingHeaders()
    {
        $response = $this->response;
        $response->addHeader('TestHeaderNotAppending', 'TestValue0001');
        $response->addHeader('TestHeaderNotAppending', 'TestValue0002');
        $response->addHeader('TestHeaderAppending', 'TestValue0001', true);
        $response->addHeader('TestHeaderAppending', 'TestValue0001', true);
        $response->addHeader('TestHeaderAppending', 'TestValue0001', true);
        $response->addHeader('TestHeaderAppending', 'TestValue0001', true);
        $this->assertSame(
            "TestHeaderNotAppending: TestValue0002\r\nTestHeaderAppending: TestValue0001\r\nTestHeaderAppending: TestValue0001\r\nTestHeaderAppending: TestValue0001\r\nTestHeaderAppending: TestValue0001\r\n\r\n",
            $response->getHeaderString()
        );
    }

    /**
     * Test's if the body content will be returned correctly
     */
    public function testGetBodyContent()
    {
        $response = $this->response;
        $testContent = 'copyBodyStreamTestContent000000000000000000000';
        $response->appendBodyStream($testContent);
        $this->assertSame($testContent, $response->getBodyContent());
    }

    /**
     * Test's if an empty body content will be returned correctly
     */
    public function testGetEmptyBodyContent()
    {
        $response = $this->response;
        $testContent = '';
        $response->appendBodyStream($testContent);
        $this->assertSame($testContent, $response->getBodyContent());
    }

    /**
     * Test's the copy body stream method without arguments
     */
    public function testCopyBodyStreamWithoutArguments()
    {
        $response = $this->response;
        $testContent = 'copyBodyStreamTestContent';
        $memStream = fopen('php://memory', 'w+');
        fwrite($memStream, $testContent);
        $response->copyBodyStream($memStream);
        rewind($response->getBodyStream());
        $actualRequestContent = fread($response->getBodyStream(), 1024);
        $this->assertSame($testContent, $actualRequestContent);
    }

    /**
     * Test's the copy body stream method with maxlength but without offset
     */
    public function testCopyBodyStreamWithMaxLengthButWithoutOffset()
    {
        $response = $this->response;
        $testContent = 'copyBodyStreamTestContent';
        $memStream = fopen('php://memory', 'w+');
        $maxlength = 10;
        fwrite($memStream, $testContent);
        $response->copyBodyStream($memStream, $maxlength);
        rewind($response->getBodyStream());
        $actualRequestContent = fread($response->getBodyStream(), 1024);
        $this->assertSame(substr($testContent, 0, $maxlength), $actualRequestContent);
    }

    /**
     * Test's the copy body stream method with maxlength and offset
     */
    public function testCopyBodyStreamWithMaxLengthAndOffset()
    {
        $response = $this->response;
        $testContent = 'copyBodyStreamTestContent';
        $memStream = fopen('php://memory', 'w+');
        $maxlength = 14;
        $offset = 5;
        fwrite($memStream, $testContent);
        $response->copyBodyStream($memStream, $maxlength, $offset);
        rewind($response->getBodyStream());
        $actualRequestContent = fread($response->getBodyStream(), 1024);
        $this->assertSame(substr($testContent, $offset, $maxlength), $actualRequestContent);
    }

    /**
     * Test's the copy body stream method without arguments on a socket stream source
     * @link https://github.com/appserver-io/http/issues/76
     */
    public function testHttpRequestCopyBodyStreamWithoutArgumentsOnSocketStream()
    {
        $response = $this->response;
        $testContent = 'copyBodyStreamTestContent';
        $streamServer = stream_socket_server('tcp://127.0.0.1:31337');
        $streamClient = fsockopen('tcp://127.0.0.1:31337');
        if (!$streamClient) {
            throw new \Exception("Unable to create socket");
        }
        $streamConnection = stream_socket_accept($streamServer);
        fwrite($streamConnection, $testContent);
        $response->copyBodyStream($streamClient, strlen($testContent));
        rewind($response->getBodyStream());
        $actualRequestContent = fread($response->getBodyStream(), strlen($testContent));
        $this->assertSame($testContent, $actualRequestContent);
        // close all sockets
        fclose($streamConnection);
        fclose($streamClient);
        fclose($streamServer);
    }

    /**
     * Test's the copy body stream method with given Offset but without maxlength
     */
    public function testCopyBodyStreamWithOffset()
    {
        $response = $this->response;
        $testException = null;
        try {
            $response->copyBodyStream(fopen('php://memory', 'r'), null, 4);
        } catch (\InvalidArgumentException $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    /**
     * Test's get non existing header on response
     */
    public function testGetHeaderWhenNonExists()
    {
        $response = $this->response;
        $testException = null;
        $testValue = null;
        try {
            $testValue = $response->getHeader('NonExistingHeader');
        } catch (\Exception $e)  {
            $testException = $e;
        }
        $this->assertSame(null, $testValue);
        $this->assertInstanceOf('AppserverIo\Http\HttpException', $testException);
    }

    /**
     * Test's get and set headers on response
     */
    public function testGetAndSetHeaders()
    {
        $response = $this->response;
        $headersToSet = array(
            'Content-Length' => 12345,
            'Host' => 'localhost',
            'Content-Type' => 'text/plain'
        );
        $response->setHeaders($headersToSet);
        $this->assertSame($headersToSet, $response->getHeaders());
    }

    /**
     * Test's to remove a header
     */
    public function testRemoveAHeader()
    {
        $response = $this->response;
        $headersToSet = array(
            'Content-Length' => 12345,
            'Host' => 'localhost',
            'Content-Type' => 'text/plain'
        );
        $response->setHeaders($headersToSet);
        $response->removeHeader('Content-Length');
        $this->assertSame(array(
            'Host' => 'localhost',
            'Content-Type' => 'text/plain'
        ), $response->getHeaders());
    }

    /**
     * Test's get and set cookies on response
     */
    public function testGetAndSetCookies()
    {
        $response = $this->response;
        $cookiesToSet = array(
            'cookie01' => new HttpCookie('cookie01'),
            'cookie02' => new HttpCookie('cookie02'),
            'cookie03' => new HttpCookie('cookie03'),
        );
        $response->setCookies($cookiesToSet);
        $this->assertSame($cookiesToSet, $response->getCookies());
    }

    /**
     * Test's has cookie when exists on response
     */
    public function testHasCookieWhenExists()
    {
        $response = $this->response;
        $response->addCookie(new HttpCookie('testcookie01'));
        $this->assertSame(true, $response->hasCookie('testcookie01'));
    }

    /**
     * Test's has cookie when not exists on response
     */
    public function testHasCookieWhenNotExists()
    {
        $response = $this->response;
        $this->assertSame(false, $response->hasCookie('testcookie01'));
    }

    /**
     * Test's get cookie when exists on response
     */
    public function testGetCookieWhenExists()
    {
        $response = $this->response;
        $cookie = new HttpCookie('testcookie01');
        $response->addCookie($cookie);
        $this->assertSame($cookie, $response->getCookie('testcookie01'));
    }

    /**
     * Test's get cookie when not exists on response
     *
     * @expectedException AppserverIo\Http\HttpException
     */
    public function testGetCookieWhenNotExists()
    {
        $response = $this->response;
        $response->getCookie('testcookie01');
    }

    /**
     * Test's the set status function
     */
    public function testSetStatus()
    {
        $response = $this->response;
        $response->setStatus('404 Not Found');
        $this->assertSame('404', $response->getStatusCode());
        $this->assertSame('Not Found', $response->getStatusReasonPhrase());
    }

    /**
     * Test's get and set state
     */
    public function testGetAndSetState()
    {
        $response = $this->response;
        $state = rand(1, 99);
        $response->setState($state);
        $this->assertSame($state, $response->getState());
    }

    /**
     * Test's get and set version
     */
    public function testGetAndSetVersion()
    {
        $response = $this->response;
        $version = '1.1';
        $response->setVersion($version);
        $this->assertSame($version, $response->getVersion());
    }


    /**
     * Test's has state on correct state
     */
    public function testHasStateOnCorrectState()
    {
        $response = $this->response;
        $state = rand(1, 99);
        $response->setState($state);
        $this->assertSame(true, $response->hasState($state));
    }

    /**
     * Test's has state on incorrect state
     */
    public function testHasStateOnIncorrectState()
    {
        $response = $this->response;
        $state = rand(1, 99);
        $response->setState($state);
        $this->assertSame(false, $response->hasState(999));
    }

}

<?php
/**
 * \AppserverIo\Http\HttpRequestTest
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
 * Class HttpRequestTest
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */
class HttpRequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Holds the part implementation
     *
     * @var HttpRequest
     */
    protected $request;

    /**
     * Initializes parser object to test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->request = new HttpRequest();
    }

    /**
     * Fills the request object with dummy data
     */
    public function fillRequestWithDummyData()
    {
        $request = $this->request;
        $request->setVersion('HTTP/1.1');
        $request->addHeader('Content-Type', 'text/plain');
        $request->addCookie(new HttpCookie('testCookie'));
        $request->addPart(new HttpPart());
        $request->appendBodyStream('testContent0000000000000000000');
        $request->setMethod('POST');
        $request->setQueryString('param1=value1&param2=value2');
        $request->setUri('http://127.0.0.0:12345/testUri/?' . $request->getQueryString());
        $request->setParams(array(
            'param1' => 'value1',
            'param2' => 'value2'
        ));
    }

    /**
     * Test's the init function
     */
    public function testHttpRequestInit()
    {
        $this->fillRequestWithDummyData();
        $request = $this->request;
        $cleanRequest = new HttpRequest();
        // reset stream to compare objects
        $cleanRequest->setBodyStream(null);
        // init request and unset stream to compare objects with phpunit
        $request->init();
        $request->setBodyStream(null);
        // check if its correct reinited
        $this->assertEquals($request, $cleanRequest);
    }

    /**
     * Test's if http request body stream is a resource after calling init
     * @see https://github.com/appserver-io/http/issues/79
     */
    public function testInitBodyStreamToBeAResource()
    {
        $request = $this->request;
        $this->assertSame(true, is_resource($request->getBodyStream()));
    }

    public function testHttpHeaderCaseInsensitivity()
    {
        $request = $this->request;
        $request->addHeader('Content-Length', 123);
        $request->addHeader('accept-encoding', 'gzip, deflate');
        $request->addHeader('cache_control', 'max-age=0');
        $request->addHeader('upGraDe_inSecurE-RequEsts', 1);
        $request->addHeader('CLIENT_IP', '0.0.0.0');
        $request->addHeader('HOST', 'localhost');
        $this->assertTrue($request->hasHeader('Content-Length'));
        $this->assertTrue($request->hasHeader('Accept-Encoding'));
        $this->assertTrue($request->hasHeader('Cache-Control'));
        $this->assertTrue($request->hasHeader('Upgrade-Insecure-Requests'));
        $this->assertTrue($request->hasHeader('Client-Ip'));
        $this->assertTrue($request->hasHeader('Host'));
    }

    /**
     * Test's the copy body stream method without arguments
     */
    public function testHttpRequestCopyBodyStreamWithoutArguments()
    {
        $request = $this->request;
        $testContent = 'copyBodyStreamTestContent';
        $memStream = fopen('php://memory', 'w+');
        fwrite($memStream, $testContent);
        $request->copyBodyStream($memStream);
        rewind($request->getBodyStream());
        $actualRequestContent = fread($request->getBodyStream(), 1024);
        $this->assertSame($testContent, $actualRequestContent);
    }

    /**
     * Test's the copy body stream method with maxlength but without offset
     */
    public function testHttpRequestCopyBodyStreamWithMaxLengthButWithoutOffset()
    {
        $request = $this->request;
        $testContent = 'copyBodyStreamTestContent';
        $memStream = fopen('php://memory', 'w+');
        $maxlength = 10;
        fwrite($memStream, $testContent);
        $request->copyBodyStream($memStream, $maxlength);
        rewind($request->getBodyStream());
        $actualRequestContent = fread($request->getBodyStream(), 1024);
        $this->assertSame(substr($testContent, 0, $maxlength), $actualRequestContent);
    }

    /**
     * Test's the copy body stream method with maxlength and offset
     */
    public function testHttpRequestCopyBodyStreamWithMaxLengthAndOffset()
    {
        $request = $this->request;
        $testContent = 'copyBodyStreamTestContent';
        $memStream = fopen('php://memory', 'w+');
        $maxlength = 14;
        $offset = 5;
        fwrite($memStream, $testContent);
        $request->copyBodyStream($memStream, $maxlength, $offset);
        rewind($request->getBodyStream());
        $actualRequestContent = fread($request->getBodyStream(), 1024);
        $this->assertSame(substr($testContent, $offset, $maxlength), $actualRequestContent);
    }

    /**
     * Test's the copy body stream method without arguments on a socket stream source
     * @link https://github.com/appserver-io/http/issues/76
     */
    public function testHttpRequestCopyBodyStreamWithoutArgumentsOnSocketStream()
    {
        $request = $this->request;
        $testContent = 'copyBodyStreamTestContent';
        $streamServer = stream_socket_server('tcp://127.0.0.1:31337');
        $streamClient = fsockopen('tcp://127.0.0.1:31337');
        if (!$streamClient) {
            throw new \Exception("Unable to create socket");
        }
        $streamConnection = stream_socket_accept($streamServer);
        fwrite($streamConnection, $testContent);
        $request->copyBodyStream($streamClient, strlen($testContent));
        rewind($request->getBodyStream());
        $actualRequestContent = fread($request->getBodyStream(), strlen($testContent));
        $this->assertSame($testContent, $actualRequestContent);
        // close all sockets
        fclose($streamConnection);
        fclose($streamClient);
        fclose($streamServer);
    }

    /**
     * Test's the copy body stream method with given Offset but without maxlength
     */
    public function testHttpRequestCopyBodyStreamWithOffset()
    {
        $request = $this->request;
        $testException = null;
        try {
            $request->copyBodyStream(fopen('php://memory', 'r'), null, 4);
        } catch (\InvalidArgumentException $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('InvalidArgumentException', $testException);
    }

    /**
     * Test's if the body content will be returned correctly
     */
    public function testHttpRequestGetBodyContent()
    {
        $request = $this->request;
        $testContent = 'copyBodyStreamTestContent000000000000000000000';
        // add correct content-length to request header to be able to read body content as string from stream
        $request->addHeader('Content-Length', strlen($testContent));
        $request->appendBodyStream($testContent);
        $this->assertSame($testContent, $request->getBodyContent());
    }

    /**
     * Test's if the empty body content will be returned correctly
     */
    public function testHttpRequestGetEmptyBodyContent()
    {
        $request = $this->request;
        $testContent = "";
        // add correct content-length to request header to be able to read body content as string from stream
        $request->addHeader('Content-Length', strlen($testContent));
        $request->appendBodyStream($testContent);
        $this->assertSame($testContent, $request->getBodyContent());
    }

    /**
     * Test's set and get functionality on http request object
     */
    public function testHttpRequestSetAndGetParam()
    {
        $request = $this->request;
        $request->setParam('param1', 'value1');
        $request->setParam('param2', 'value2');
        $request->setParam('param3', 'value3');
        $this->assertSame('value1', $request->getParam('param1'));
        $this->assertSame('value2', $request->getParam('param2'));
        $this->assertSame('value3', $request->getParam('param3'));
    }

    /**
     * Test's get non existing param on http request object
     */
    public function testHttpRequestGetParamWhenNonExists()
    {
        $request = $this->request;
        $testValue = $request->getParam('NonExistingParam');
        $this->assertSame(null, $testValue);
    }

    /**
     * Test's has non existing param on http request object
     */
    public function testHttpRequestHasParamWhenNonExists()
    {
        $request = $this->request;
        $this->assertFalse($request->hasParam('NonExistingParam'));
    }

    /**
     * Test's has existing param on http request object
     */
    public function testHttpRequestHasParamWhenExists()
    {
        $request = $this->request;
        $request->setParam('param1', 'value1');
        $this->assertTrue($request->hasParam('param1'));
    }

    /**
     * Test's get non existing part on http request object
     */
    public function testHttpRequestGetPartWhenNonExists()
    {
        $request = $this->request;
        $testValue = $request->getPart('NonExistingPart');
        $this->assertSame(null, $testValue);
    }

    /**
     * Test's get non existing header on http request object
     */
    public function testHttpRequestGetHeaderWhenNonExists()
    {
        $request = $this->request;
        $testValue = $request->getHeader('NonExistingHeader');
        $this->assertSame(null, $testValue);
    }

    /**
     * Test's has header when exists on http request object
     */
    public function testHttpRequestHasHeaderWhenExists()
    {
        $request = $this->request;
        $request->addHeader('Content-Length', 123);
        $this->assertSame(true, $request->hasHeader('Content-Length'));
    }

    /**
     * Test's has header when not exists on http request object
     */
    public function testHttpRequestHasHeaderWhenNotExists()
    {
        $request = $this->request;
        $this->assertSame(false, $request->hasHeader('Content-Length'));
    }

    /**
     * Test's has cookie when exists on http request object
     */
    public function testHttpRequestHasCookieWhenExists()
    {
        $request = $this->request;
        $request->addCookie(new HttpCookie('testcookie01'));
        $this->assertSame(true, $request->hasCookie('testcookie01'));
    }

    /**
     * Test's has cookie when not exists on http request object
     */
    public function testHttpRequestHasCookieWhenNotExists()
    {
        $request = $this->request;
        $this->assertSame(false, $request->hasCookie('testcookie01'));
    }

    /**
     * Test's get cookie when exists on http request object
     */
    public function testHttpRequestGetCookieWhenExists()
    {
        $request = $this->request;
        $cookie = new HttpCookie('testcookie01');
        $request->addCookie($cookie);
        $this->assertSame($cookie, $request->getCookie('testcookie01'));
    }

    /**
     * Test's get cookie when not exists on http request object
     */
    public function testHttpRequestGetCookieWhenNotExists()
    {
        $request = $this->request;
        $this->assertSame(null, $request->getCookie('testcookie01'));
    }

    /**
     * Test's get and set headers on http request object
     */
    public function testHttpRequestGetAndSetHeaders()
    {
        $request = $this->request;
        $headersToSet = array(
            'Content-Length' => 12345,
            'Host' => 'localhost',
            'Content-Type' => 'text/plain'
        );
        $request->setHeaders($headersToSet);
        $this->assertSame($headersToSet, $request->getHeaders());
    }

    /**
     * Test's get and set cookies on http request object
     */
    public function testHttpRequestGetAndSetCookies()
    {
        $request = $this->request;
        $cookiesToSet = array(
            'cookie01' => new HttpCookie('cookie01'),
            'cookie02' => new HttpCookie('cookie02'),
            'cookie03' => new HttpCookie('cookie03'),
        );
        $request->setCookies($cookiesToSet);
        $this->assertSame($cookiesToSet, $request->getCookies());
    }

    /**
     * Test's get and set uri on http request object
     */
    public function testHttpRequestGetAndSetUri()
    {
        $request = $this->request;
        $uriToSet = 'http://127.0.0.0:12345/testUri/?param1=value1&param2=value2';
        $request->setUri($uriToSet);
        $this->assertSame($uriToSet, $request->getUri());
    }

    /**
     * Test's get params on http request object
     */
    public function testHttpRequestGetParams()
    {
        $request = $this->request;
        $request->setParam('param1', 'value1');
        $request->setParam('param2', 'value2');
        $request->setParam('param3', 'value3');
        $this->assertSame(array(
            'param1' => 'value1',
            'param2' => 'value2',
            'param3' => 'value3'
        ), $request->getParams());
    }

}

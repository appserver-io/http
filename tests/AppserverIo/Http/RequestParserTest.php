<?php
/**
 * \AppserverIo\Http\RequestParserTest
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
class RequestParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HttpRequestParser
     */
    public $parser;

    /**
     * Initializes parser object to test.
     *
     * @return void
     */
    public function setUp()
    {
        $request = new HttpRequest();
        $response = new HttpResponse();
        // init parser by req and res
        $this->parser = new HttpRequestParser($request, $response);
        $this->parser->injectQueryParser(new HttpQueryParser());
        $this->parser->injectPart(new HttpPart());
    }

    /**
     * Test multipart parsing with empty values
     */
    public function testMultiPartParsingWithEmptyValues()
    {
        $multiPartBodyContent = "------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[__state]\"\r\n\r\nTzozMzoiVFlQTzNcRm9ybVxDb3JlXFJ1bnRpbWVcRm9ybVN0YXRlIjoyOntzOjI1OiIAKgBsYXN0RGlzcGxheWVkUGFnZUluZGV4IjtpOjA7czoxMzoiACoAZm9ybVZhbHVlcyI7YTowOnt9fQ==18325156e7fe63abd938069d4a08ae7dc95e2af6\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[__trustedProperties]\"\r\n\r\na:4:{s:4:\"site\";i:1;s:10:\"packageKey\";i:1;s:8:\"siteName\";i:1;s:13:\"__currentPage\";i:1;}264c581c4bacda4bda4ae728e84632d8547a5e6a\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"__csrfToken\"\r\n\r\nefbb4f9c952d3b0a8af54aff0a144090\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[site]\"\r\n\r\nTYPO3.NeosDemoTypo3Org\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[packageKey]\"\r\n\r\n\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[siteName]\"\r\n\r\n\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[__currentPage]\"\r\n\r\n1\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX--\r\n";
        $expectedResult = array(
            '--siteImportStep' =>
                array(
                    '__state' => 'TzozMzoiVFlQTzNcRm9ybVxDb3JlXFJ1bnRpbWVcRm9ybVN0YXRlIjoyOntzOjI1OiIAKgBsYXN0RGlzcGxheWVkUGFnZUluZGV4IjtpOjA7czoxMzoiACoAZm9ybVZhbHVlcyI7YTowOnt9fQ==18325156e7fe63abd938069d4a08ae7dc95e2af6',
                    '__trustedProperties' => 'a:4:{s:4:"site";i:1;s:10:"packageKey";i:1;s:8:"siteName";i:1;s:13:"__currentPage";i:1;}264c581c4bacda4bda4ae728e84632d8547a5e6a',
                    'site' => 'TYPO3.NeosDemoTypo3Org',
                    'packageKey' => '',
                    'siteName' => '',
                    '__currentPage' => '1',
                ),
            '__csrfToken' => 'efbb4f9c952d3b0a8af54aff0a144090',
        );
        $request = $this->parser->getRequest();
        $request->addHeader(HttpProtocol::HEADER_CONTENT_TYPE, 'multipart/form-data; boundary=----WebKitFormBoundaryOO68wjlGqJLTz1AX');
        $this->parser->parseMultipartFormData($multiPartBodyContent);
        $result = $this->parser->getQueryParser()->getResult();
        $this->assertSame(var_export($result, true), var_export($expectedResult, true));
    }

    /**
     * Test multipart parsing with values
     */
    public function testMultiPartParsingWithValues()
    {
        $multiPartBodyContent = "------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[__state]\"\r\n\r\nTzozMzoiVFlQTzNcRm9ybVxDb3JlXFJ1bnRpbWVcRm9ybVN0YXRlIjoyOntzOjI1OiIAKgBsYXN0RGlzcGxheWVkUGFnZUluZGV4IjtpOjA7czoxMzoiACoAZm9ybVZhbHVlcyI7YTowOnt9fQ==546f5a1ab0b920821aaf34cdfb858717d666789b\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[__trustedProperties]\"\r\n\r\na:6:{s:6:\"driver\";i:1;s:4:\"user\";i:1;s:8:\"password\";i:1;s:4:\"host\";i:1;s:6:\"dbname\";i:1;s:13:\"__currentPage\";i:1;}6257cab62f04dfa156df41fccd93ee87ba816ebe\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"__csrfToken\"\r\n\r\n23ca48201b96c207678c6154b0445444\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[driver]\"\r\n\r\npdo_mysql\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[user]\"\r\n\r\nroot\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[password]\"\r\n\r\npassword\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[host]\"\r\n\r\n127.0.0.1\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[dbname]\"\r\n\r\nNEOSASDFASDF\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81\r\nContent-Disposition: form-data; name=\"--databaseStep[__currentPage]\"\r\n\r\n1\r\n------WebKitFormBoundaryUmLBdln8JKrYyp81--\r\n";
        $expectedResult = array(
            '--databaseStep' =>
                array(
                    '__state' => 'TzozMzoiVFlQTzNcRm9ybVxDb3JlXFJ1bnRpbWVcRm9ybVN0YXRlIjoyOntzOjI1OiIAKgBsYXN0RGlzcGxheWVkUGFnZUluZGV4IjtpOjA7czoxMzoiACoAZm9ybVZhbHVlcyI7YTowOnt9fQ==546f5a1ab0b920821aaf34cdfb858717d666789b',
                    '__trustedProperties' => 'a:6:{s:6:"driver";i:1;s:4:"user";i:1;s:8:"password";i:1;s:4:"host";i:1;s:6:"dbname";i:1;s:13:"__currentPage";i:1;}6257cab62f04dfa156df41fccd93ee87ba816ebe',
                    'driver' => 'pdo_mysql',
                    'user' => 'root',
                    'password' => 'password',
                    'host' => '127.0.0.1',
                    'dbname' => 'NEOSASDFASDF',
                    '__currentPage' => '1',
                ),
            '__csrfToken' => '23ca48201b96c207678c6154b0445444',
        );
        $request = $this->parser->getRequest();
        $request->addHeader(HttpProtocol::HEADER_CONTENT_TYPE, 'multipart/form-data; boundary=----WebKitFormBoundaryUmLBdln8JKrYyp81');
        $this->parser->parseMultipartFormData($multiPartBodyContent);
        $result = $this->parser->getQueryParser()->getResult();
        $this->assertSame(var_export($result, true), var_export($expectedResult, true));
    }

    /**
     * Test multipart parsing with one boundary
     */
    public function testMultiPartFormDataParsingToGetPartsFromRequestWithOneBoundary()
    {
        $bodyContent = "------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=\"file1\"; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\nTestContentTest0000\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(1, count($this->parser->getRequest()->getParts()));
        // get part instance for file1
        $part = $this->parser->getRequest()->getPart('file1');
        $this->assertInstanceOf('AppserverIo\Psr\HttpMessage\PartInterface', $part);
        $this->assertSame("testUpload.txt", $part->getFilename());
        $this->assertSame(19, $part->getSize());
        $this->assertSame("text/plain", $part->getContentType());
        $this->assertSame("TestContentTest0000", stream_get_contents($part->getInputStream()));
        // check headers on part
        $this->assertSame(array(
            'Content-Disposition' => 'form-data; name="file1"; filename="testUpload.txt"',
            'Content-Type' => 'text/plain'
        ), $part->getHeaders());
        // check headername on part
        $this->assertSame(array(
            0 => 'Content-Disposition',
            1 => 'Content-Type'
        ), $part->getHeaderNames());
    }

    /**
     * Test multipart parsing with more boundaries
     */
    public function testMultiPartFormDataParsingToGetPartsFromRequestWithMoreBoundaries()
    {
        $bodyContent = "------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=\"file1\"; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\n\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=\"file2\"; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\n\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=\"file3\"; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\n\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI--\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(3, count($this->parser->getRequest()->getParts()));
    }

    /**
     * Test multipart parsing with different boundary information set in header
     */
    public function testMultiPartFormDataParsingToGetPartsFromRequestWithOneBoundaryAndDifferentBoundaryHeaderInformation()
    {
        $bodyContent = "------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=\"file1\"; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\nTestContentTest0000\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryBU2f3FHUr7h9fwenj\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(0, count($this->parser->getRequest()->getParts()));
    }

    /**
     * Test multipart parsing with invalid boundary info in header
     */
    public function testMultiPartFormDataParsingToGetPartsFromRequestWithOneInvalidBoundaryInHeader()
    {
        $bodyContent = "------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=\"file1\"; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\nTestContentTest0000\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(0, count($this->parser->getRequest()->getParts()));
    }

    /**
     * Test multipart parsing with no name set in body content boundary info
     */
    public function testMultiPartFormDataParsingToGetPartsFromRequestWithNoNameSetInBodyContentBoundaryInfo()
    {
        $bodyContent = "------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\nContent-Disposition: form-data; name=; filename=\"testUpload.txt\"\r\nContent-Type: text/plain\r\n\r\nTestContentTest0000\r\n------WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryBUm5KAhIP4YY4UcI\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(0, count($this->parser->getRequest()->getParts()));
    }

    /**
     * Test multipart parsing with normal params without a value
     */
    public function testMultiPartFormDataParsingToGetParamsFromRequestWithoutValues()
    {
        $bodyContent = "------WebKitFormBoundary2ggasILZfbBBMaCb\r\nContent-Disposition: form-data; name=\"testParam1\"\r\n\r\n\r\n------WebKitFormBoundary2ggasILZfbBBMaCb\r\nContent-Disposition: form-data; name=\"testParam2\"\r\n\r\n\r\n------WebKitFormBoundary2ggasILZfbBBMaCb--\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundary2ggasILZfbBBMaCb\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(
            $this->parser->getQueryParser()->getResult(),
            array(
                'testParam1' => '',
                'testParam2' => ''
            )
        );
    }

    /**
     * Test multipart parsing with normal params and values
     */
    public function testMultiPartFormDataParsingToGetParamsFromRequestWithValues()
    {
        $bodyContent = "------WebKitFormBoundaryIvj0dNQtwxACEjEk\r\nContent-Disposition: form-data; name=\"testParam1\"\r\n\r\ntestValue1\r\n------WebKitFormBoundaryIvj0dNQtwxACEjEk\r\nContent-Disposition: form-data; name=\"testParam2\"\r\n\r\ntestValue2\r\n------WebKitFormBoundaryIvj0dNQtwxACEjEk--\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryIvj0dNQtwxACEjEk\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(
            $this->parser->getQueryParser()->getResult(),
            array(
                'testParam1' => 'testValue1',
                'testParam2' => 'testValue2'
            )
        );
    }

    /**
     * Test multipart parsing with a mp4 video
     */
    public function testMultiPartFormDataParsingToGetParamsFromRequestWithMP4Video()
    {
        $videoBody = "AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAABgNtZGF0AAACrgYF//+q3EXpvebZSLeWLNgg2SPu73gyNjQgLSBjb3JlIDE0OCByMjY0MyA1YzY1NzA0IC0gSC4yNjQvTVBFRy00IEFWQyBjb2RlYyAtIENvcHlsZWZ0IDIwMDMtMjAxNSAtIGh0dHA6Ly93d3cudmlkZW9sYW4ub3JnL3gyNjQuaHRtbCAtIG9wdGlvbnM6IGNhYmFjPTEgcmVmPTMgZGVibG9jaz0xOjA6MCBhbmFseXNlPTB4MzoweDExMyBtZT1oZXggc3VibWU9NyBwc3k9MSBwc3lfcmQ9MS4wMDowLjAwIG1peGVkX3JlZj0xIG1lX3JhbmdlPTE2IGNocm9tYV9tZT0xIHRyZWxsaXM9MSA4eDhkY3Q9MSBjcW09MCBkZWFkem9uZT0yMSwxMSBmYXN0X3Bza2lwPTEgY2hyb21hX3FwX29mZnNldD0tMiB0aHJlYWRzPTQgbG9va2FoZWFkX3RocmVhZHM9MSBzbGljZWRfdGhyZWFkcz0wIG5yPTAgZGVjaW1hdGU9MSBpbnRlcmxhY2VkPTAgYmx1cmF5X2NvbXBhdD0wIGNvbnN0cmFpbmVkX2ludHJhPTAgYmZyYW1lcz0zIGJfcHlyYW1pZD0yIGJfYWRhcHQ9MSBiX2JpYXM9MCBkaXJlY3Q9MSB3ZWlnaHRiPTEgb3Blbl9nb3A9MCB3ZWlnaHRwPTIga2V5aW50PTI1MCBrZXlpbnRfbWluPTI1IHNjZW5lY3V0PTQwIGludHJhX3JlZnJlc2g9MCByY19sb29rYWhlYWQ9NDAgcmM9Y3JmIG1idHJlZT0xIGNyZj0yMy4wIHFjb21wPTAuNjAgcXBtaW49MCBxcG1heD02OSBxcHN0ZXA9NCBpcF9yYXRpbz0xLjQwIGFxPTE6MS4wMACAAAAAMGWIhAA3//728P4FNlYEUJcRzeidMx+/Fbi6PXL2RZj2wmZZxBesACRgA0YoZdXQoQAAAAxBmiRsQ3/+p4QAl4AAAAAJQZ5CeIV/AHpB3gIATGF2YzU2LjYwLjEwMABCIAjBGDghEARgjBwAAAAJAZ5hdEJ/AKCAIRAEYIwcIRAEYIwcAAAACQGeY2pCfwCggSEQBGCMHAAAABJBmmhJqEFomUwIb//+p4QAl4EhEARgjBwhEARgjBwAAAALQZ6GRREsK/8AekEhEARgjBwAAAAJAZ6ldEJ/AKCBIRAEYIwcIRAEYIwcAAAACQGep2pCfwCggCEQBGCMHAAAABJBmqxJqEFsmUwIb//+p4QAl4AhEARgjBwhEARgjBwAAAALQZ7KRRUsK/8AekEhEARgjBwhEARgjBwAAAAJAZ7pdEJ/AKCAIRAEYIwcAAAACQGe62pCfwCggCEQBGCMHCEQBGCMHAAAABJBmvBJqEFsmUwIb//+p4QAl4EhEARgjBwAAAALQZ8ORRUsK/8AekEhEARgjBwhEARgjBwAAAAJAZ8tdEJ/AKCBIRAEYIwcAAAACQGfL2pCfwCggCEQBGCMHCEQBGCMHAAAABJBmzRJqEFsmUwIb//+p4QAl4AhEARgjBwhEARgjBwAAAALQZ9SRRUsK/8AekEhEARgjBwAAAAJAZ9xdEJ/AKCAIRAEYIwcIRAEYIwcAAAACQGfc2pCfwCggCEQBGCMHAAAABFBm3hJqEFsmUwIZ//+nhACTyEQBGCMHCEQBGCMHAAAAAtBn5ZFFSwr/wB6QCEQBGCMHAAAAAkBn7V0Qn8AoIEhEARgjBwhEARgjBwAAAAJAZ+3akJ/AKCBIRAEYIwcAAAAEUGbvEmoQWyZTAhf//6MsAJWIRAEYIwcIRAEYIwcAAAAC0Gf2kUVLCv/AHpBIRAEYIwcIRAEYIwcAAAACQGf+XRCfwCggCEQBGCMHAAAAAkBn/tqQn8AoIEhEARgjBwhEARgjBwAAAASQZv+SahBbJlMFEwn//3xABZRIRAEYIwcAAAACQGeHWpCfwCggCEQBGCMHCEQBGCMHCEQBGCMHCEQBGCMHCEQBGCMHCEQBGCMHCEQBGCMHAAACURtb292AAAAbG12aGQAAAAAAAAAAAAAAAAAAAPoAAAEQAABAAABAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAAAEK3RyYWsAAABcdGtoZAAAAAMAAAAAAAAAAAAAAAEAAAAAAAAECwAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAEAAAAAAsAAAAJAAAAAAACRlZHRzAAAAHGVsc3QAAAAAAAAAAQAABAsAAAfSAAEAAAAAA6NtZGlhAAAAIG1kaGQAAAAAAAAAAAAAAAAAAHUwAAB5N1XEAAAAAAAtaGRscgAAAAAAAAAAdmlkZQAAAAAAAAAAAAAAAFZpZGVvSGFuZGxlcgAAAANObWluZgAAABR2bWhkAAAAAQAAAAAAAAAAAAAAJGRpbmYAAAAcZHJlZgAAAAAAAAABAAAADHVybCAAAAABAAADDnN0YmwAAACWc3RzZAAAAAAAAAABAAAAhmF2YzEAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAsACQAEgAAABIAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAY//8AAAAwYXZjQwFkAAv/4QAXZ2QAC6zZQsTsBEAAAPpAADqYA8UKZYABAAZo6+PLIsAAAAAYc3R0cwAAAAAAAAABAAAAHwAAA+kAAAAUc3RzcwAAAAAAAAABAAAAAQAAAQhjdHRzAAAAAAAAAB8AAAABAAAH0gAAAAEAABONAAAAAQAAB9IAAAABAAAAAAAAAAEAAAPpAAAAAQAAE40AAAABAAAH0gAAAAEAAAAAAAAAAQAAA+kAAAABAAATjQAAAAEAAAfSAAAAAQAAAAAAAAABAAAD6QAAAAEAABONAAAAAQAAB9IAAAABAAAAAAAAAAEAAAPpAAAAAQAAE40AAAABAAAH0gAAAAEAAAAAAAAAAQAAA+kAAAABAAATjQAAAAEAAAfSAAAAAQAAAAAAAAABAAAD6QAAAAEAABONAAAAAQAAB9IAAAABAAAAAAAAAAEAAAPpAAAAAQAAC7sAAAABAAAD6QAAAChzdHNjAAAAAAAAAAIAAAABAAAAAwAAAAEAAAACAAAAAQAAAAEAAACQc3RzegAAAAAAAAAAAAAAHwAAAuYAAAAQAAAADQAAAA0AAAANAAAAFgAAAA8AAAANAAAADQAAABYAAAAPAAAADQAAAA0AAAAWAAAADwAAAA0AAAANAAAAFgAAAA8AAAANAAAADQAAABUAAAAPAAAADQAAAA0AAAAVAAAADwAAAA0AAAANAAAAFgAAAA0AAACEc3RjbwAAAAAAAAAdAAAAMAAAA1AAAANpAAADfAAAA54AAAOzAAADzAAAA98AAAQBAAAEHAAABC8AAARIAAAEZAAABH8AAASSAAAEqwAABM0AAATiAAAE+wAABQ4AAAUvAAAFRAAABV0AAAVwAAAFkQAABawAAAW/AAAF2AAABfQAAARDdHJhawAAAFx0a2hkAAAAAwAAAAAAAAAAAAAAAgAAAAAAAARAAAAAAAAAAAAAAAABAQAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAJGVkdHMAAAAcZWxzdAAAAAAAAAABAAAEQAAAAAAAAQAAAAADu21kaWEAAAAgbWRoZAAAAAAAAAAAAAAAAAAAu4AAAMwAVcQAAAAAAC1oZGxyAAAAAAAAAABzb3VuAAAAAAAAAAAAAAAAU291bmRIYW5kbGVyAAAAA2ZtaW5mAAAAEHNtaGQAAAAAAAAAAAAAACRkaW5mAAAAHGRyZWYAAAAAAAAAAQAAAAx1cmwgAAAAAQAAAypzdGJsAAAAanN0c2QAAAAAAAAAAQAAAFptcDRhAAAAAAAAAAEAAAAAAAAAAAACABAAAAAAu4AAAAAAADZlc2RzAAAAAAOAgIAlAAIABICAgBdAFQAAAAAB9AAAAAlHBYCAgAURkFblAAaAgIABAgAAABhzdHRzAAAAAAAAAAEAAAAzAAAEAAAAATxzdHNjAAAAAAAAABkAAAABAAAAAgAAAAEAAAADAAAAAQAAAAEAAAAEAAAAAgAAAAEAAAAFAAAAAQAAAAEAAAAGAAAAAgAAAAEAAAAHAAAAAQAAAAEAAAAIAAAAAgAAAAEAAAAKAAAAAQAAAAEAAAALAAAAAgAAAAEAAAAMAAAAAQAAAAEAAAANAAAAAgAAAAEAAAAOAAAAAQAAAAEAAAAPAAAAAgAAAAEAAAARAAAAAQAAAAEAAAASAAAAAgAAAAEAAAATAAAAAQAAAAEAAAAUAAAAAgAAAAEAAAAVAAAAAQAAAAEAAAAWAAAAAgAAAAEAAAAXAAAAAQAAAAEAAAAYAAAAAgAAAAEAAAAaAAAAAQAAAAEAAAAbAAAAAgAAAAEAAAAcAAAAAQAAAAEAAAAdAAAABwAAAAEAAADgc3RzegAAAAAAAAAAAAAAMwAAABcAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAAYAAAAGAAAABgAAAIRzdGNvAAAAAAAAAB0AAAMzAAADXQAAA3YAAAOSAAADrQAAA8AAAAPZAAAD9QAABBAAAAQpAAAEPAAABF4AAARzAAAEjAAABJ8AAATBAAAE3AAABO8AAAUIAAAFIwAABT4AAAVRAAAFagAABYUAAAWgAAAFuQAABcwAAAXuAAAGAQAAAGJ1ZHRhAAAAWm1ldGEAAAAAAAAAIWhkbHIAAAAAAAAAAG1kaXJhcHBsAAAAAAAAAAAAAAAALWlsc3QAAAAlqXRvbwAAAB1kYXRhAAAAAQAAAABMYXZmNTYuNDAuMTAx";
        $bodyContent = "------WebKitFormBoundary0UtlU0OEOMCbmK39\r\nContent-Disposition: form-data; name=\"file\"; filename=\"blank.mp4\"\r\nContent-Type: video/mp4\r\n\r\n".base64_decode($videoBody)."\r\n------WebKitFormBoundary0UtlU0OEOMCbmK39--\r\n";
        $headerString = "Host: 127.0.0.1\r\n" .
            "Connection: keep-alive\r\n" .
            "Content-Length: " . strlen($bodyContent) . "\r\n" .
            "Cache-Control: max-age=0\r\n" .
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
            "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36\r\n" .
            "Content-Type: multipart/form-data; boundary=----WebKitFormBoundary0UtlU0OEOMCbmK39\r\n" .
            "Accept-Encoding: gzip,deflate,sdch\r\n" .
            "Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
            "Cookie: _ga=GA1.1.1798004689.1405528669\r\n";
        $this->parser->parseHeaders($headerString);
        $this->parser->parseMultipartFormData($bodyContent);
        $this->assertSame(1, count($this->parser->getRequest()->getParts()));
        // get part instance for file1
        $part = $this->parser->getRequest()->getPart('file');
        $this->assertInstanceOf('AppserverIo\Psr\HttpMessage\PartInterface', $part);
        $this->assertSame("blank.mp4", $part->getFilename());
        $this->assertSame("video/mp4", $part->getContentType());
        $this->assertSame($videoBody, base64_encode(stream_get_contents($part->getInputStream())));
        // check headers on part
        $this->assertSame(array(
            'Content-Disposition' => 'form-data; name="file"; filename="blank.mp4"',
            'Content-Type' => 'video/mp4'
        ), $part->getHeaders());
        // check headername on part
        $this->assertSame(array(
            0 => 'Content-Disposition',
            1 => 'Content-Type'
        ), $part->getHeaderNames());
    }

    /**
     * Test to parse a long start line
     */
    public function testParseLongStartLine()
    {
        $startLine = 'GET /test/../test/../test/../?%40action=checkConnection&__widgetContext=TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573&driver=pdo_mysql&user=root&password=password&host=127.0.0.1&_=1396280213348 HTTP/1.1' . "\r\n";
        $this->parser->parseStartLine($startLine);
        $request = $this->parser->getRequest();
        $this->assertSame($request->getMethod(), 'GET');
        $this->assertSame($request->getVersion(), 'HTTP/1.1');
        $this->assertSame($request->getUri(), '/?%40action=checkConnection&__widgetContext=TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573&driver=pdo_mysql&user=root&password=password&host=127.0.0.1&_=1396280213348');
        $this->assertSame($request->getQueryString(), '%40action=checkConnection&__widgetContext=TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573&driver=pdo_mysql&user=root&password=password&host=127.0.0.1&_=1396280213348');
    }

    /**
     * Test to parse a invalid start line
     */
    public function testParseInvalidStartLine()
    {
        $startLine = 'KILL / HTTP/8.8' . "\r\n";
        $testException = null;
        try {
            $this->parser->parseStartLine($startLine);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('AppserverIo\Http\HttpException', $testException);
    }

    /**
     * Test to parse cookie header with one cookie in it
     */
    public function testCookieHeaderParsingWithOneCookie()
    {
        $requestHeaders = "Host: www.test.local\r\n" .
            "Cookie: testcookiename0001=fq38o74fQFQFHf73h48fh837hq34fq34fq34q4; \r\n" .
            "Connection: close";
        $this->parser->parseHeaders($requestHeaders);
        // get cookie from collection
        $cookies = $this->parser->getRequest()->getCookies();
        $cookie = $cookies['testcookiename0001'];
        // check if values are correct
        $this->assertSame($cookie->getName(), 'testcookiename0001');
        $this->assertSame($cookie->getValue(), 'fq38o74fQFQFHf73h48fh837hq34fq34fq34q4');
    }

    /**
     * Test to parse cookie header with many cookie in it
     */
    public function testCookieHeaderParsingWithManyCookie()
    {
        $requestHeaders = "Host: www.test.local\r\n" .
            "Cookie: testcookiename0001=1111; testcookiename0002=2222; testcookiename0003=3333; testcookiename0004=4444; testcookiename0005=5555; \r\n" .
            "Connection: close";
        $this->parser->parseHeaders($requestHeaders);
        // get cookies from collection
        $cookies = $this->parser->getRequest()->getCookies();
        // iterate and check values
        for ($i = 1; $i <= 5; $i++) {
            // check if values are correct
            $this->assertSame($cookies["testcookiename000$i"]->getName(), "testcookiename000$i");
            $this->assertSame($cookies["testcookiename000$i"]->getValue(), "$i$i$i$i");
        }
    }

    /**
     * Test the header parsing with an empty header string
     */
    public function testHeaderParsingWithEmptyString()
    {
        $parser = $this->parser;
        $headerString = '';
        $testException = null;
        try {
            $parser->parseHeaders($headerString);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('AppserverIo\Http\HttpException', $testException);
    }

    /**
     * Test the header line parsing wrong format
     */
    public function testHeaderLineParsingWithWrongHeaderFormat()
    {
        $parser = $this->parser;
        $headerLine = 'Content-Length = 123';
        $testException = null;
        try {
            $parser->parseHeaderLine($headerLine);
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('AppserverIo\Http\HttpException', $testException);
    }

    /**
     * Test the init function of the http parser instance
     */
    public function testInitFunction()
    {
        $parser = $this->parser;
        // build up request
        $parser->parseStartLine('GET / HTTP/1.1');
        $parser->parseHeaderLine('Host: localhost');
        // build up response
        $parser->getResponse()->setStatus(200);
        $parser->getResponse()->appendBodyStream('Hello World');
        // get clean request and response objects
        $cleanRequest = new HttpRequest();
        $cleanResponse = new HttpResponse();
        // remove those resources to be able to compare them via php unit
        $cleanRequest->setBodyStream(null);
        $cleanResponse->setBodyStream(null);

        // finally init parser
        $parser->init();
        // remove those resources to be able to compare them via php unit
        $parser->getRequest()->setBodyStream(null);
        $parser->getResponse()->setBodyStream(null);

        $this->assertEquals($cleanRequest, $parser->getRequest());
        $this->assertEquals($cleanResponse, $parser->getResponse());
    }

    /**
     * Test the normalizing functionality with path info and encoding parts in uri
     */
    public function testUriNormalizingWithPathInfoAndEncoding()
    {
        $parser = $this->parser;
        $uri = '/test1/test2/..%2F/%2F/%2F/%2F%2F%2Ftest3/test33/../..%2F../test4/./.%2Ftest5%2F../test9/../index.php/test/../test/./test/..?test=test../%2F%20../../';
        $this->assertSame("/test4/index.php/test/?test=test../%2F%20../../", $parser::normalizeUri($uri));
    }

    /**
     * Test uri normalizing on unsecure uri with possible directory traversal attack
     *
     * @expectedException \AppserverIo\Http\HttpException
     */
    public function testUriNormalizingOnUnsecureUri()
    {
        $parser = $this->parser;
        $uri = '/%2F..%2F..%2F..%2F';
        $parser::normalizeUri($uri);
    }

    /**
     * Test start line parsing when relative uri path was given
     *
     * @expectedException \AppserverIo\Http\HttpException
     */
    public function testInvalidStartLineParsingWithRelativeUri()
    {
        $parser = $this->parser;
        $uri = 'test/%2F..%2F..%2F..%2F';
        $parser->parseStartLine('GET ' . $uri . ' HTTP/1.1' . "\r\n");
    }

    /**
     * Test uri normalizer resturns original uri if nothing should be normalized
     */
    public function testUriNormalizerReturnsOriginalUriIfNothingShouldBeNormalized()
    {
        $parser = $this->parser;
        $uri = '/test/test.html';
        $this->assertSame($uri, $parser::normalizeUri($uri));
    }

    /**
     * Test uri normalizer resturns original uri with query string if nothing should be normalized
     */
    public function testUriNormalizerReturnsOriginalUriWithQueryStringIfNothingShouldBeNormalized()
    {
        $parser = $this->parser;
        $uri = '/test/test.html?testVar=../../&anotherTestVar=http://test.de';
        $this->assertSame($uri, $parser::normalizeUri($uri));
    }

    /**
     * Test uri normalizer doesn't replace the + char with an empty string
     * @link https://github.com/appserver-io/appserver/issues/944
     */
    public function testUriNormalizerWithPlusChar()
    {
        $parser = $this->parser;
        $uri = '/mac/appserver-dist_1.1.0-95+beta7_x86_64.pkg';
        $this->assertSame($uri, $parser::normalizeUri($uri));
    }

    /**
     * Test uri normalizer does replace script tags with an escaped version of them
     * @link https://github.com/appserver-io/appserver/issues/944
     */
    public function testUriNormalizerEscapesScriptTagsInUri()
    {
        $parser = $this->parser;
        $uri = '/test/<script>alert("TEST");</script>';
        $this->assertSame('/test/&lt;script&gt;alert(&quot;TEST&quot;);&lt;/script&gt;', $parser::normalizeUri($uri));
    }

    /**
     * Test uri normalizer doesn't replace umlaut characters with their HTML escaped version
     * @link https://github.com/appserver-io/appserver/issues/1016
     */
    public function testUriNormalizerWithUmlautCharacters()
    {
        $parser = $this->parser;
        $uri = '/test/müllmänner.jpg';
        $this->assertSame($uri, $parser::normalizeUri($uri));
    }
}

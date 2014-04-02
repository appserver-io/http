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
}

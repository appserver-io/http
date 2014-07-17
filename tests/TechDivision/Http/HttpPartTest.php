<?php
/**
 * \TechDivision\Http\HttpPartTest
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

use TechDivision\Http\HttpPart;

/**
 * Class HttpPartTest
 *
 * @category   Library
 * @package    TechDivision_Http
 * @subpackage tests
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_Http
 */
class HttpPartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Holds the part implementation
     *
     * @var HttpPart
     */
    protected $part;

    /**
     * Initializes parser object to test.
     *
     * @return void
     */
    public function setUp() {
        $this->part = new HttpPart();
    }

    /**
     * Test's the http part object instantiation without giving constructor arguments
     */
    public function testHttpPartInstantiationWithoutConstructorArguments()
    {
        $part = $this->part->getInstance();
        $this->assertSame($part->getName(), null);
        $this->assertSame($part->getContentType(), null);
        $this->assertSame($part->getFilename(), null);
        $this->assertSame($part->getHeaderNames(), array());
        $this->assertSame($part->getHeaders(), array());
        $this->assertSame($part->getSize(), 0);
        $this->assertSame(is_resource($part->getInputStream()), true);
    }

    /**
     * Test's the http part object instantiation with invalid stream wrapper given
     */
    public function testHttpPartInstantiationWithInvalidStreamWrapper()
    {
        $testException = null;
        try {
            $part = $this->part->getInstance('php://nonExistingType');
        } catch (\Exception $e) {
            $testException = $e;
        }
        $this->assertInstanceOf('\Exception', $testException);
    }

    /**
     * Test's http part write functionality
     */
    public function testHttpPartWriteMethod()
    {
        $part = $this->part->getInstance();
        $partFilename = md5(time());
        $part->setFilename($partFilename);
        $part->setName('testPartName');
        $part->addHeader('Content-Type', 'text/plain');
        $part->putContent('testContent0000000');
        $fsFilename = tempnam(sys_get_temp_dir(), 'testHttpPartWriteFunctionality');
        $part->write($fsFilename);
        // check if file exists
        $this->assertSame(true, is_file($fsFilename));
        // read in file where part has written to
        $content = file_get_contents($fsFilename);
        $this->assertSame($content, 'testContent0000000');
    }

    /**
     * Test's the get headers functionality by getting one specific header by name
     */
    public function testHttpPartHeadersGetOneSpecific()
    {
        $part = $this->part->getInstance();
        $part->addHeader('Content-Type', 'text/plain');
        $part->addHeader('Content-Disposition', 'form-data; name="file1"; filename="testUpload.txt"');
        $this->assertSame($part->getHeaders('Content-Type'), 'text/plain');
        $this->assertSame($part->getHeaders('Content-Disposition'), 'form-data; name="file1"; filename="testUpload.txt"');
    }

    /**
     * Test's http part delete functionality
     */
    public function testHttpPartDeleteMethod()
    {
        $part = $this->part->getInstance();
        $partFilename = md5(time());
        $part->setFilename($partFilename);
        $part->setName('testPartName');
        $part->addHeader('Content-Type', 'text/plain');
        $part->putContent('testContent0000000');
        $part->delete();
        $this->assertSame(is_resource($part->getInputStream()), false);
    }
}
 
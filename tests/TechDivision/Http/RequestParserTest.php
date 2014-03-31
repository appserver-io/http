<?php
/**
 * \TechDivision\Http\RequestParserTest
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
class RequestParserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var HttpRequestParser
     */
    public $parser;

    /**
     * Initializes parser object to test.
     *
     * @return void
     */
    public function setUp() {
        $request = new HttpRequest();
        $response = new HttpResponse();
        // init parser by req and res
        $this->parser = new HttpRequestParser($request, $response);
        $this->parser->injectQueryParser(new HttpQueryParser());
        $this->parser->injectPart(new HttpPart());
    }

    /**
     * Test multipart parsing
     */
    public function testMultiPartParsing() {
        $multiPartBodyContent = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_files/multiPartBodyContent001.txt');
        $request = $this->parser->getRequest();
        $request->addHeader(HttpProtocol::HEADER_CONTENT_TYPE, 'multipart/form-data; boundary=----WebKitFormBoundaryaQdwdIDo9mKQdyJQ');
        $this->parser->parseMultipartFormData($multiPartBodyContent);
        $result = $this->parser->getQueryParser()->getResult();
        $this->assertSame($result, array (
            '--databaseStep' =>
                array (
                    '__state' => 'TzozMzoiVFlQTzNcRm9ybVxDb3JlXFJ1bnRpbWVcRm9ybVN0YXRlIjoyOntzOjI1OiIAKgBsYXN0RGlzcGxheWVkUGFnZUluZGV4IjtpOjA7czoxMzoiACoAZm9ybVZhbHVlcyI7YTowOnt9fQ==ecd205bc939b500eb2fb977d0e24a3fd90ac6f72',
                    '__trustedProperties' => 'a:6:{s:6:"driver";i:1;s:4:"user";i:1;s:8:"password";i:1;s:4:"host";i:1;s:6:"dbname";i:1;s:13:"__currentPage";i:1;}cfb1594e56810eaf65e05041b75a764c30991b14',
                    'driver' => 'pdo_mysql',
                    'user' => 'root',
                    'password' => 'password',
                    'host' => '127.0.0.1',
                    'dbname' => 'NeosTest006',
                    '__currentPage' => '1',
                ),
            '__csrfToken' => 'ad517faaae804530f70139f9925df2d1',
        ));
    }

    /**
     * Test to parse a long start line
     */
    public function testParseLongStartLine()
    {
        $startLine = 'GET /?%40action=checkConnection&__widgetContext=TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573&driver=pdo_mysql&user=root&password=password&host=127.0.0.1&_=1396280213348 HTTP/1.1' . "\r\n";
        $this->parser->parseStartLine($startLine);
        $request = $this->parser->getRequest();
        $this->assertSame($request->getMethod(), 'GET');
        $this->assertSame($request->getVersion(), 'HTTP/1.1');
        $this->assertSame($request->getQueryString(), '%40action=checkConnection&__widgetContext=TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573&driver=pdo_mysql&user=root&password=password&host=127.0.0.1&_=1396280213348');
    }
}

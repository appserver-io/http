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
     * Test multipart parsing with empty values
     */
    public function testMultiPartParsingWithEmptyValues()
    {
        $multiPartBodyContent = "------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[__state]\"\r\n\r\nTzozMzoiVFlQTzNcRm9ybVxDb3JlXFJ1bnRpbWVcRm9ybVN0YXRlIjoyOntzOjI1OiIAKgBsYXN0RGlzcGxheWVkUGFnZUluZGV4IjtpOjA7czoxMzoiACoAZm9ybVZhbHVlcyI7YTowOnt9fQ==18325156e7fe63abd938069d4a08ae7dc95e2af6\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[__trustedProperties]\"\r\n\r\na:4:{s:4:\"site\";i:1;s:10:\"packageKey\";i:1;s:8:\"siteName\";i:1;s:13:\"__currentPage\";i:1;}264c581c4bacda4bda4ae728e84632d8547a5e6a\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"__csrfToken\"\r\n\r\nefbb4f9c952d3b0a8af54aff0a144090\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[site]\"\r\n\r\nTYPO3.NeosDemoTypo3Org\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[packageKey]\"\r\n\r\n\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[siteName]\"\r\n\r\n\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX\r\nContent-Disposition: form-data; name=\"--siteImportStep[__currentPage]\"\r\n\r\n1\r\n------WebKitFormBoundaryOO68wjlGqJLTz1AX--\r\n";
        $expectedResult = array (
            '--siteImportStep' =>
                array (
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
        $expectedResult = array (
            '--databaseStep' =>
                array (
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

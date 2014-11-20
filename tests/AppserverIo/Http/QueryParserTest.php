<?php
/**
 * \AppserverIo\Http\HttpQueryParserTest
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
 * Class HttpQueryParserTest
 *
 * @category  Library
 * @package   Http
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2014 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 */
class HttpQueryParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Hold's the query parser instance
     *
     * @var HttpQueryParser
     */
    public $queryParser;

    /**
     * Initializes request object to test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->queryParser = new HttpQueryParser();
    }

    /**
     * Tests parse string functionality with empty queryString
     */
    public function testParseStrFunctionWithEmptyString()
    {
        $this->queryParser->parseStr('');
        $this->assertSame(array(), $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality with filled queryString
     */
    public function testParseStrFunctionWithNonEmptyQueryString()
    {
        $this->queryParser->parseStr('key-1=value-1&key-2=value-2&key-3=value-3');

        $expectedResult = array(
            'key-1' => 'value-1',
            'key-2' => 'value-2',
            'key-3' => 'value-3'
        );

        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality with filled queryString
     * and leading question mark
     */
    public function testParseStrFunctionWithNonEmptyQueryStringAndLeadingQuestionMark()
    {
        $this->queryParser->parseStr('?key-3=value-1&key-2=value-2&key-1=value-3');

        $expectedResult = array(
            'key-3' => 'value-1',
            'key-2' => 'value-2',
            'key-1' => 'value-3'
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality with queryString containing only question mark
     */
    public function testParseStrFunctionWithQueryStringContainingOnlyQuestionMark()
    {
        $this->queryParser->parseStr('?');

        $this->assertSame(array(), $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality with filled queryString
     * and leading question mark url encoded
     */
    public function testParseStrFunctionWithNonEmptyEncodedQueryStringAndLeadingQuestionMark()
    {
        $queryString = '?' . urlencode('@key-3') . '=' . urlencode('@value-1') . '&' .
            urlencode('key-2') . '=' . urlencode('value-2') . '&' .
            urlencode('key-1') . '=' . urlencode('value-3');

        $this->queryParser->parseStr($queryString);

        $expectedResult = array(
            '@key-3' => '@value-1',
            'key-2' => 'value-2',
            'key-1' => 'value-3'
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality with filled queryString
     * and leading question mark url encoded twice
     */
    public function testParseStrFunctionWithNonEmptyDoubleEncodedQueryStringAndLeadingQuestionMark()
    {
        $queryString = '?' . (urlencode(urlencode('@key-3'))) . '=' . urlencode(urlencode('@value-1')) . '&' .
            urlencode(urlencode('key-2')) . '=' . urlencode(urlencode('value-2')) . '&' .
            urlencode(urlencode('key-1')) . '=' . urlencode(urlencode('value-3'));

        $this->queryParser->parseStr($queryString);

        $expectedResult = array(
            '%40key-3' => '%40value-1',
            'key-2' => 'value-2',
            'key-1' => 'value-3'
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests key value parsing with array structured key and numeric indices
     */
    public function testParseKeyValueFunctionWithArrayStructuredKeyWithNumericIndex()
    {
        $keys[] = 'key[][2][3][4][5]';
        $keys[] = 'key[11][2][3][4][5]';
        $keys[] = 'key[][2][3][4][5]';
        $value = 'testValue';

        foreach ($keys as $key) {
            $this->queryParser->parseKeyValue($key, $value);
        }

        $expectedResult = array(
            'key' => array(
                0 => array(
                    2 => array(
                        3 => array(
                            4 => array(
                                5 => 'testValue'
                            )
                        )
                    )
                ),
                11 => array(
                    2 => array(
                        3 => array(
                            4 => array(
                                5 => 'testValue'
                            )
                        )
                    )
                ),
                12 => array(
                    2 => array(
                        3 => array(
                            4 => array(
                                5 => 'testValue'
                            )
                        )
                    )
                )
            )
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests key value parsing function with array structured key and dynamic indices
     */
    public function testParseKeyValueFunctionWithArrayStructuredKeyWithDynamicIndex()
    {
        $key = 'key[level-1][level-2][level-3]';
        $value = 'testValue';

        $this->queryParser->parseKeyValue($key, $value);

        $expectedResult = array(
            'key' => array(
                'level-1' => array(
                    'level-2' => array(
                        'level-3' => 'testValue'
                    )
                )
            )
        );

        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests result after clear query parser
     */
    public function testClearNonEmptyQueryParserResult()
    {
        $this->queryParser->parseStr('key1[111]=value');
        $this->queryParser->parseStr('key2[aaa]=value');

        $this->queryParser->clear();
        $this->assertSame(array(), $this->queryParser->getResult());
    }

    /**
     * tests key value parsing function with same array structured key and different values
     */
    public function testParseKeyValueFunctionWithSameArrayStructuredKeyWithDifferentValues()
    {
        $keys[] = 'test';
        $keys[] = 'test';
        $keys[] = 'key[level-1][level-2][level-3]';
        $keys[] = 'key[level-1][level-22][level-33]';
        $keys[] = 'key[level-1][level-22][level-34]';
        $keys[] = 'key[level-1][level-22]';
        $keys[] = 'key[level-1][level-23]';
        $keys[] = 'key[level-1][level-23]';
        $value = 'testValue';

        foreach ($keys as $key) {
            $this->queryParser->parseKeyValue($key, $value);
        }

        $expectedResult = array(
            'test' => 'testValue',
            'key' => array(
                'level-1' => array(
                    'level-2' => array(
                        'level-3' => 'testValue'
                    ),
                    'level-22' => array(
                        'level-33' => 'testValue',
                        'level-34' => 'testValue',
                        0 => 'testValue',
                    ),
                    'level-23' => 'testValue'
                )
            )
        );

        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Test to parse application/x-www-form-urlencoded request body string
     */
    public function testParseXWWWFormUrlEncodedBodyString()
    {
        $requestBody = '__referrer%5B%40package%5D=TYPO3.Setup&__referrer%5B%40subpackage%5D=&__referrer%5B%40controller%5D=Login&__referrer%5B%40action%5D=login&__referrer%5Barguments%5D=YTowOnt9bf4f81e2ed786c268e0907de67f5ddc54b615cae&__trustedProperties=a%3A3%3A%7Bs%3A16%3A%22__authentication%22%3Ba%3A1%3A%7Bs%3A5%3A%22TYPO3%22%3Ba%3A1%3A%7Bs%3A4%3A%22Flow%22%3Ba%3A1%3A%7Bs%3A8%3A%22Security%22%3Ba%3A1%3A%7Bs%3A14%3A%22Authentication%22%3Ba%3A1%3A%7Bs%3A5%3A%22Token%22%3Ba%3A1%3A%7Bs%3A13%3A%22PasswordToken%22%3Ba%3A1%3A%7Bs%3A8%3A%22password%22%3Bi%3A1%3B%7D%7D%7D%7D%7D%7D%7Ds%3A4%3A%22step%22%3Bi%3A1%3Bi%3A0%3Bi%3A1%3B%7D315741b77b33b12b54462c6aa61c34fa081e32d8&__csrfToken=ad517faaae804530f70139f9925df2d1&__authentication%5BTYPO3%5D%5BFlow%5D%5BSecurity%5D%5BAuthentication%5D%5BToken%5D%5BPasswordToken%5D%5Bpassword%5D=hudtLN3x&step=0';
        $this->queryParser->parseStr($requestBody);
        $result = $this->queryParser->getResult();
        $this->assertSame($result, array (
                '__referrer' =>
                    array (
                        '@package' => 'TYPO3.Setup',
                        '@subpackage' => '',
                        '@controller' => 'Login',
                        '@action' => 'login',
                        'arguments' => 'YTowOnt9bf4f81e2ed786c268e0907de67f5ddc54b615cae',
                    ),
                '__trustedProperties' => 'a:3:{s:16:"__authentication";a:1:{s:5:"TYPO3";a:1:{s:4:"Flow";a:1:{s:8:"Security";a:1:{s:14:"Authentication";a:1:{s:5:"Token";a:1:{s:13:"PasswordToken";a:1:{s:8:"password";i:1;}}}}}}}s:4:"step";i:1;i:0;i:1;}315741b77b33b12b54462c6aa61c34fa081e32d8',
                '__csrfToken' => 'ad517faaae804530f70139f9925df2d1',
                '__authentication' =>
                    array (
                        'TYPO3' =>
                            array (
                                'Flow' =>
                                    array (
                                        'Security' =>
                                            array (
                                                'Authentication' =>
                                                    array (
                                                        'Token' =>
                                                            array (
                                                                'PasswordToken' =>
                                                                    array (
                                                                        'password' => 'hudtLN3x',
                                                                    ),
                                                            ),
                                                    ),
                                            ),
                                    ),
                            ),
                    ),
                'step' => '0',
            )
        );
    }

    /**
     * Test to parse a long query string
     */
    public function testParseLongQueryString()
    {
        $queryString = '%40action=checkConnection&__widgetContext=TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573&driver=pdo_mysql&user=root&password=password&host=127.0.0.1&_=1396280213348';
        $this->queryParser->parseStr($queryString);
        $result = $this->queryParser->getResult();
        $this->assertSame($result, array (
            '@action' => 'checkConnection',
            '__widgetContext' => 'TzozNzoiVFlQTzNcRmx1aWRcQ29yZVxXaWRnZXRcV2lkZ2V0Q29udGV4dCI6NDp7czoxOToiACoAd2lkZ2V0SWRlbnRpZmllciI7czo1NzoidHlwbzMtc2V0dXAtdmlld2hlbHBlcnMtd2lkZ2V0LWRhdGFiYXNlc2VsZWN0b3J2aWV3aGVscGVyIjtzOjIzOiIAKgBhamF4V2lkZ2V0SWRlbnRpZmllciI7TjtzOjI2OiIAKgBhamF4V2lkZ2V0Q29uZmlndXJhdGlvbiI7YTo5OntzOjIxOiJkcml2ZXJEcm9wZG93bkZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kcml2ZXIiO3M6MTE6InVzZXJGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtdXNlciI7czoxNToicGFzc3dvcmRGaWVsZElkIjtzOjIxOiJkYXRhYmFzZVN0ZXAtcGFzc3dvcmQiO3M6MTE6Imhvc3RGaWVsZElkIjtzOjE3OiJkYXRhYmFzZVN0ZXAtaG9zdCI7czoxNzoiZGJOYW1lVGV4dEZpZWxkSWQiO3M6MTk6ImRhdGFiYXNlU3RlcC1kYm5hbWUiO3M6MjE6ImRiTmFtZURyb3Bkb3duRmllbGRJZCI7czoyODoiZGF0YWJhc2VTdGVwLWRibmFtZS1kcm9wZG93biI7czoxNzoic3RhdHVzQ29udGFpbmVySWQiO3M6MjY6ImRhdGFiYXNlU3RlcC1kYm5hbWUtc3RhdHVzIjtzOjI1OiJtZXRhZGF0YVN0YXR1c0NvbnRhaW5lcklkIjtzOjM1OiJkYXRhYmFzZVN0ZXAtZGJuYW1lLW1ldGFkYXRhLXN0YXR1cyI7czo4OiJ3aWRnZXRJZCI7Tjt9czoyMzoiACoAY29udHJvbGxlck9iamVjdE5hbWUiO3M6Njg6IlRZUE8zXFNldHVwXFZpZXdIZWxwZXJzXFdpZGdldFxDb250cm9sbGVyXERhdGFiYXNlU2VsZWN0b3JDb250cm9sbGVyIjt9b42869a28db333e870e3df6e4bd866fb571fc573',
            'driver' => 'pdo_mysql',
            'user' => 'root',
            'password' => 'password',
            'host' => '127.0.0.1',
            '_' => '1396280213348',
        ));
    }

    /**
     * Test to parse url encoded values with ampersand in its value and first param with empty value
     */
    public function testParseUrlEncodedValuesWithAmpersandInItsValuesAndEmptyParamFirst()
    {
        $queryStr = "sender=&receiver=test%40techdivision.com&subject=TEST-+(67806+%5BHauptgetriebe%5D)+%232328682&id=1700935&lang=DE&diagnosis=Es+sind+lediglich+geringe+Abweichungen+im+Vergleich+mit+der+vorherigen+Probe+feststellbar.%0A++++++++++Falls+noch+kein+%C3%96lwechsel+erfolgt+ist%2C+w%C3%A4re+eine+weitere+Verwendung+des+%C3%96les+bei+%C3%A4hnlichen%0A++++++++++Betriebsbedingungen+unter+Beibehaltung+%C3%BCblicher+Wartungsarbeiten+m%C3%B6glich.+Ich+rate+Ihnen%3A+Senden+Sie%0A++++++++++uns+die+n%C3%A4chste+Probe+bei+Ihrer+n%C3%A4chsten+Wartung+oder+anl%C3%A4sslich+der+%C3%BCblichen+Inspektion+zu+einer%0A++++++++++Beobachtung+des+Trendverhaltens.%0A++++++&message=yadsasd%25C3%25B6asdasd%2526ycxyc%250A";
        $this->queryParser->parseStr($queryStr);
        $result = $this->queryParser->getResult();

        $this->assertSame($result, array (
            'sender' => '',
            'receiver' => "test@techdivision.com",
            'subject' => "TEST- (67806 [Hauptgetriebe]) #2328682",
            'id' => "1700935",
            'lang' => "DE",
            'diagnosis' => "Es sind lediglich geringe Abweichungen im Vergleich mit der vorherigen Probe feststellbar.
          Falls noch kein Ölwechsel erfolgt ist, wäre eine weitere Verwendung des Öles bei ähnlichen
          Betriebsbedingungen unter Beibehaltung üblicher Wartungsarbeiten möglich. Ich rate Ihnen: Senden Sie
          uns die nächste Probe bei Ihrer nächsten Wartung oder anlässlich der üblichen Inspektion zu einer
          Beobachtung des Trendverhaltens.
      ",
            'message' => "yadsasd%C3%B6asdasd%26ycxyc%0A",
        ));
    }

    /**
     * test false urlencoded querystring where the whole querystring was urlencoded
     */
    public function testWrongQueryStringWhichIsTotallyUrlEncoded()
    {
        $queryStr = "sender%3D%26receiver%3Dtest%2540techdivision.com%26subject%3D%2B%2867806%2B%255B%255D%29%2B%25232328682%26id%3D1700935%26lang%3DDE%26diagnosis%3DEs%2Bsind%2Blediglich%2Bgeringe%2BAbweichungen%2Bim%2BVergleich%2Bmit%2Bder%2Bvorherigen%2BProbe%2Bfeststellbar.%250A%2B%2B%2B%2B%2B%2B%2B%2B%2B%2BFalls%2Bnoch%2Bkein%2B%25C3%2596lwechsel%2Berfolgt%2Bist%252C%2Bw%25C3%25A4re%2Beine%2Bweitere%2BVerwendung%2Bdes%2B%25C3%2596les%2Bbei%2B%25C3%25A4hnlichen%250A%2B%2B%2B%2B%2B%2B%2B%2B%2B%2BBetriebsbedingungen%2Bunter%2BBeibehaltung%2B%25C3%25BCblicher%2BWartungsarbeiten%2Bm%25C3%25B6glich.%2BIch%2Brate%2BIhnen%253A%2BSenden%2BSie%250A%2B%2B%2B%2B%2B%2B%2B%2B%2B%2Buns%2Bdie%2Bn%25C3%25A4chste%2BProbe%2Bbei%2BIhrer%2Bn%25C3%25A4chsten%2BWartung%2Boder%2Banl%25C3%25A4sslich%2Bder%2B%25C3%25BCblichen%2BInspektion%2Bzu%2Beiner%250A%2B%2B%2B%2B%2B%2B%2B%2B%2B%2BBeobachtung%2Bdes%2BTrendverhaltens.%250A%2B%2B%2B%2B%2B%2B%26message%3Dyadsasd%2525C3%2525B6asdasd%252526ycxyc%25250A";
        $this->queryParser->parseStr($queryStr);
        $result = $this->queryParser->getResult();
        $this->assertSame($result, array(
            "sender=&receiver=test%40techdivision.com&subject=+(67806+%5B%5D)+%232328682&id=1700935&lang=DE&diagnosis=Es+sind+lediglich+geringe+Abweichungen+im+Vergleich+mit+der+vorherigen+Probe+feststellbar.%0A++++++++++Falls+noch+kein+%C3%96lwechsel+erfolgt+ist%2C+w%C3%A4re+eine+weitere+Verwendung+des+%C3%96les+bei+%C3%A4hnlichen%0A++++++++++Betriebsbedingungen+unter+Beibehaltung+%C3%BCblicher+Wartungsarbeiten+m%C3%B6glich.+Ich+rate+Ihnen%3A+Senden+Sie%0A++++++++++uns+die+n%C3%A4chste+Probe+bei+Ihrer+n%C3%A4chsten+Wartung+oder+anl%C3%A4sslich+der+%C3%BCblichen+Inspektion+zu+einer%0A++++++++++Beobachtung+des+Trendverhaltens.%0A++++++&message=yadsasd%25C3%25B6asdasd%2526ycxyc%250A" => ""
        ));
    }

    /**
     * Tests parse string functionality where one param has no value
     */
    public function testParseStrFunctionWhereOneParamHasNoValue()
    {
        $queryString = '?wsdl';

        $this->queryParser->parseStr($queryString);

        $expectedResult = array('wsdl' => '');
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality where more params have no value
     */
    public function testParseStrFunctionWhereMoreParamHaveNoValues()
    {
        $queryString = '?wsdl&test1&test2&test3&test4';

        $this->queryParser->parseStr($queryString);

        $expectedResult = array(
            'wsdl' => '',
            'test1' => '',
            'test2' => '',
            'test3' => '',
            'test4' => ''
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Test's parsing relevant check depending on given content type
     */
    public function testIfContentTypeIsParsingRelevant()
    {
        $contentType = 'multipart/form-data';
        $this->assertSame(true, $this->queryParser->isParsingRelevant($contentType));
    }

    /**
     * Test's parsing relevant check depending on given content type which is not parsing relevant
     */
    public function testIfContentTypeIsNotParsingRelevant()
    {
        $contentType = 'text/plain';
        $this->assertSame(false, $this->queryParser->isParsingRelevant($contentType));
    }
}

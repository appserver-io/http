<?php

/**
 * \AppserverIo\Http\BasicAuthenticationTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH - <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io/
 */

namespace AppserverIo\Http;

use AppserverIo\Http\Authentication\BasicAuthentication;

/**
 * Class for testing the basic authentication feature
 *
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH - <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io/
 */
class BasicAuthenticationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Config to build our test class with
     *
     * @var string[] $config
     */
    public $config = array();

    /**
     * Property holding an instance of our basic authentication implementation
     *
     * @var \AppserverIo\Http\Authentication\BasicAuthentication $testClass
     */
    public $testClass;

    /**
     * Renew the test class before every run.
     * Default algorithm is crypt
     *
     * @return void
     */
    public function setUp()
    {
        $this->config = array(
            'type' => '\AppserverIo\Http\Authentication\BasicAuthentication',
            'realm' => 'Basic-Test-Realm',
            'file' => __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'htpasswd_crypt'
        );
        $this->testClass = new BasicAuthentication($this->config);
    }

    /**
     * Tests if we can generate a correct auth header
     *
     * @return void
     */
    public function testGetAuthenticateHeader()
    {
        $header = $this->testClass->getAuthenticateHeader();
        $this->assertContains('Basic', $header);
        $this->assertContains('Basic-Test-Realm', $header);
    }
}

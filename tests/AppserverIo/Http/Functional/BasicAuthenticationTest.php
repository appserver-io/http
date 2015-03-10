<?php

/**
 * \AppserverIo\Http\Functional\BasicAuthenticationTest
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

namespace AppserverIo\Http\Functional;

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
        $this->initTestEnvironment('htpasswd_crypt');
    }

    /**
     * Will initialize the test environment
     *
     * @param $testFile
     *
     * @return void
     */
    public function initTestEnvironment($testFile)
    {
        $this->config = array(
            'type' => '\AppserverIo\Http\Authentication\BasicAuthentication',
            'realm' => 'Basic-Test-Realm',
            'file' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $testFile
        );

        $this->testClass = new BasicAuthentication($this->config);
    }

    /**
     * Returns a basic auth string invalid against the default credentials
     *
     * @return string
     */
    public function getInvalidAuthString()
    {
        return 'Basic c2RnZmg6c2RmaA==';
    }

    /**
     * Returns a basic auth string valid against the default credentials
     *
     * @return string
     */
    public function getValidAuthString()
    {
        return 'Basic YXBwc2VydmVyOmFwcHNlcnZlci5pMA==';
    }

    /**
     * Will test if we can detect missing adapter options for file based htpasswd adapter
     *
     * @return void
     *
     * @expectedException \AppserverIo\Http\Authentication\AuthenticationException
     * @expectedExceptionMessageRegExp /Missing or invalid "file"/
     */
    public function testBrokenCredentialFile()
    {
        // create ourselves a new test instance
        $this->initTestEnvironment('I_do_not_exist_at_all');
    }

    /**
     * Tests if we are deflected using an invalid auth string and the default adapter with a crypt hashed password
     *
     * @return void
     */
    public function testInvalidAuthDefaultAdapterCryptAlgorithm()
    {
        $this->testClass->init($this->getInvalidAuthString(), 'POST');
        $this->assertFalse($this->testClass->authenticate());
    }

    /**
     * Tests if we are deflected using an invalid auth string and the default adapter with a Apr1Md5 hashed password
     *
     * @return void
     */
    public function testInvalidAuthDefaultAdapterApr1md5Algorithm()
    {
        // create ourselves a new test instance
        $this->initTestEnvironment('htpasswd_apr1md5');

        $this->testClass->init($this->getInvalidAuthString(), 'POST');
        $this->assertFalse($this->testClass->authenticate());
    }

    /**
     * Tests if we are deflected using an invalid auth string and the default adapter with a Sha1 hashed password
     *
     * @return void
     */
    public function testInvalidAuthDefaultAdapterSha1Algorithm()
    {
        // create ourselves a new test instance
        $this->initTestEnvironment('htpasswd_sha1');

        $this->testClass->init($this->getInvalidAuthString(), 'POST');
        $this->assertFalse($this->testClass->authenticate());
    }

    /**
     * Tests if we can authenticate using a valid auth string and the default adapter with a crypt hashed password
     *
     * @return void
     */
    public function testValidAuthDefaultAdapterCryptAlgorithm()
    {
        $this->testClass->init($this->getValidAuthString(), 'POST');
        $this->assertTrue($this->testClass->authenticate());
    }

    /**
     * Tests if we can authenticate using a valid auth string and the default adapter with a Apr1Md5 hashed password
     *
     * @return void
     */
    public function testValidAuthDefaultAdapterApr1md5Algorithm()
    {
        // create ourselves a new test instance
        $this->initTestEnvironment('htpasswd_apr1md5');

        $this->testClass->init($this->getValidAuthString(), 'POST');
        $this->assertTrue($this->testClass->authenticate());
    }

    /**
     * Tests if we can authenticate using a valid auth string and the default adapter with a Sha1 hashed password
     *
     * @return void
     */
    public function testValidAuthDefaultAdapterSha1Algorithm()
    {
        // create ourselves a new test instance
        $this->initTestEnvironment('htpasswd_sha1');

        $this->testClass->init($this->getValidAuthString(), 'POST');
        $this->assertTrue($this->testClass->authenticate());
    }

    /**
     * Tests if the client auth data gets parsed correctly
     *
     * @return void
     */
    public function testParseCorrectCredentials()
    {
        $this->testClass->init($this->getValidAuthString(), 'POST');
        $this->assertEquals('appserver', $this->testClass->getUsername());
        $this->assertEquals('appserver.i0', $this->testClass->getPassword());
    }
}

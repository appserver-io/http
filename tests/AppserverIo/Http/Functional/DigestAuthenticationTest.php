<?php

/**
 * \AppserverIo\Http\Functional\DigestAuthenticationTest
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

use AppserverIo\Http\Authentication\DigestAuthentication;

/**
 * Class for testing the digest authentication feature
 *
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH - <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io/
 */
class DigestAuthenticationTest extends \PHPUnit_Framework_TestCase
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
     * @var \AppserverIo\Http\Authentication\DigestAuthentication $testClass
     */
    public $testClass;

    /**
     * Renew the test class before every run
     *
     * @return void
     */
    public function setUp()
    {
        $this->config = array(
            'type' => '\AppserverIo\Http\Authentication\DigestAuthentication',
            'realm' => 'Digest-Test-Realm',
            'file' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'htdigest'
        );
        $this->testClass = new DigestAuthentication($this->config);
    }

    /**
     * Returns a basic auth string invalid against the default credentials
     *
     * @return string
     */
    public function getInvalidAuthString()
    {
        return 'Digest username="sfgdg", realm="Digest-Test-Realm", nonce="54fea8f866b06", uri="/example/index.do/digestAuthentication", response="76fb66e477e4efd09bf69af906245fea", opaque="1b01e72e7d9e07e5ae195df3f4ad8244", qop=auth, nc=00000001, cnonce="02d0eeb99588e65e"';
    }

    /**
     * Returns a basic auth string valid against the default credentials
     *
     * @return string
     */
    public function getValidAuthString()
    {
        return 'Digest username="appserver", realm="Digest-Test-Realm", nonce="54fea869c8a60", uri="/example/index.do/digestAuthentication", response="6b4923cf114c0cdcfa13f570d33a7933" opaque="1b01e72e7d9e07e5ae195df3f4ad8244", qop=auth, nc=00000001, cnonce="3e296007074a5d0a"';
    }

    /**
     * Tests if we are deflected using an invalid auth string and the default adapter
     *
     * @return void
     */
    public function testInvalidAuthDefaultAdapter()
    {
        $this->testClass->init($this->getInvalidAuthString(), 'GET');
        $this->assertFalse($this->testClass->authenticate());
    }

    /**
     * Tests if we can authenticate using a valid auth string and the default adapter
     *
     * @return void
     */
    public function testValidAuthDefaultAdapter()
    {
        $this->testClass->init($this->getValidAuthString(), 'GET');
        $this->assertTrue($this->testClass->authenticate());
    }
}

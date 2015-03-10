<?php

/**
 * AppserverIo\Http\Authentication\Adapters\AbstractAdapter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Florian Sydekum <fs@techdivision.com>
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernahrd Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Http\Authentication\Adapters;

/**
 * Abstract class for authentication adapters.
 *
 * @author    Florian Sydekum <fs@techdivision.com>
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernahrd Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * The configured credentials we have authenticate against
     *
     * @var array
     */
    protected $credentials = array();

    /**
     * The configuration as taken by the authentication method
     *
     * @var array
     */
    protected $config = array();

    /**
     * Authentication methods this adapter is compatible to
     *
     * @var array $usableFor
     */
    protected static $usableFor = array();

    /**
     * Instantiates an authentication adapter.
     *
     * @param array $config The security configuration matching this adapter
     */
    public function __construct(array $config)
    {
        // initialize the configuration
        $this->config = $config;

        // initialize the adapter implementations
        $this->init();
    }

    /**
     * Whether or not an adapter is usable with a certain authentication type
     *
     * @param string $authType The type of the authentication implementation
     *
     * @return boolean
     */
    public static function isUsable($authType)
    {
        $usableFor = array_flip(static::$usableFor);
        return isset($usableFor[$authType]);
    }

    /**
     * Will return the credentials found in the local configuration
     *
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Returns the authentication adapter type token
     *
     * @return string
     */
    public function getType()
    {
        return static::ADAPTER_TYPE;
    }

    /**
     * Returns authentication configuration
     *
     * @return array The authentication options
     */
    protected function getConfig()
    {
        return $this->config;
    }
}

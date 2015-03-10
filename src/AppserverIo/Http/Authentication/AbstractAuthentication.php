<?php

/**
 * \AppserverIo\Http\Authentication\AbstractAuthentication
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
 * @author    Bernahrd Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Http\Authentication;

/**
 * Class AbstractAuthentication
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @author    Bernahrd Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
abstract class AbstractAuthentication implements AuthenticationInterface
{

    /**
     * Holds the auth data got from http authentication header
     *
     * @var \AppserverIo\Http\Authentication\Adapters\AdapterInterface $authAdapter
     */
    protected $authAdapter;

    /**
     * Holds the auth data got from http authentication header
     *
     * @var string $authData
     */
    protected $authData;

    /**
     * Holds the requests method
     *
     * @var string $reqMethod
     */
    protected $reqMethod;

    /**
     * Holds the configuration data given for authentication type
     *
     * @var array $configData
     */
    protected $configData;

    /**
     * Constructs the authentication type
     *
     * @param array $configData The configuration data for auth type instance
     */
    public function __construct(array $configData = array())
    {
        // set vars internally
        $this->configData = $configData;

        // verify the configuration
        $this->verifyConfig();

        // prepare our chosen adapter
        $this->prepareAdapter();
    }

    /**
     * Try to authenticate
     *
     * @return boolean If auth was successful return TRUE if not, FALSE will be returned
     *
     * @throws \AppserverIo\Http\Authentication\AuthenticationException
     */
    public function authenticate()
    {

        // verify everything to be ready for auth if not return false
        if (! $this->verify()) {
            return false;
        }

        // do actual authentication check
        return $this->authAdapter->authenticate($this->getAuthData());
    }

    /**
     * Returns the authentication data content
     *
     * @return string The authentication data content
     */
    public function getAuthData()
    {
        return $this->authData;
    }

    /**
     * Returns the request method
     *
     * @return string The request method
     */
    public function getRequestMethod()
    {
        return $this->reqMethod;
    }

    /**
     * Returns the authentication type token
     *
     * @return string
     */
    public function getType()
    {
        return static::AUTH_TYPE;
    }

    /**
     * Returns the parsed username
     *
     * @return string|null
     */
    public function getUsername()
    {
        $authData = $this->getAuthData();
        return isset($authData['username']) ? $authData['username'] : null;
    }

    /**
     * Initialise by the auth content got from client
     *
     * @param string $rawAuthData The content of authentication data sent by client
     * @param string $reqMethod   The https request method as string
     *
     * @return void
     */
    public function init($rawAuthData, $reqMethod)
    {
        // set vars internally
        $this->reqMethod = $reqMethod;

        // parse auth data
        $this->parse($rawAuthData);
    }

    /**
     * Will prepare the authentication class's authentication adapter based on its configuration
     *
     * @return void
     *
     * @throws \AppserverIo\Http\Authentication\AuthenticationException
     */
    protected function prepareAdapter()
    {
        // get config data to local var
        $configData = $this->configData;

        // determine the adapter type
        $adapterType = static::DEFAULT_ADAPTER;
        if (isset($configData['adapter-type']) && !empty($configData['adapter-type'])) {
            $adapterType = $configData['adapter-type'];
        }

        // initialize the adapter class
        $authAdapterClass = '\AppserverIo\Http\Authentication\Adapters\\' . ucfirst($adapterType) . 'Adapter';

        // instantiate configured authentication adapter
        if (class_exists($authAdapterClass) && $authAdapterClass::isUsable($this->getType())) {
            $this->authAdapter = new $authAdapterClass($configData);
            $this->authAdapter->init();

        } else {
            throw new AuthenticationException(sprintf('Unknown adapter type "%s" for authentication method "%s"', $adapterType, $this->getType()));
        }
    }

    /**
     * Verifies everything to be ready for authenticate for specific type
     *
     * @return boolean
     *
     * @throws \AppserverIo\Http\Authentication\AuthenticationException
     */
    public function verify()
    {
        // set internal var refs
        $authData = $this->getAuthData();

        // check if credentials are empty
        if (empty($authData)) {
            return false;
        }

        return true;
    }

    /**
     * Verifies configuration setting and throws exception
     *
     * @return void
     *
     * @throws \AppserverIo\Http\Authentication\AuthenticationException
     */
    protected function verifyConfig()
    {
        // get config data to local var
        $configData = $this->configData;

        // check auth config entry and file existence
        if (empty($configData) || ! isset($configData['realm'])) {
            throw new AuthenticationException(sprintf(AuthenticationException::MESSAGE_MIN_CONFIG_MISSING, $this->getType()));
        }
    }
}

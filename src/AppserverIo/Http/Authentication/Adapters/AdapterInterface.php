<?php

/**
 * \AppserverIo\Http\Authentication\Adapters\AdapterInterface
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

namespace AppserverIo\Http\Authentication\Adapters;

/**
 * The interface common to all authentication adapters
 *
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH - <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io/
 */
interface AdapterInterface
{

    /**
     * Authenticates based on authentication data and request method
     *
     * @param array $authData The data used to authenticate against our credentials
     *
     * @return boolean TRUE if authentication was successful, else FALSE
     */
    public function authenticate(array $authData);

    /**
     * Initializes the adapter.
     *
     * @return void
     */
    public function init();

    /**
     * Will return the credentials found in the local configuration
     *
     * @return array
     */
    public function getCredentials();

    /**
     * Returns the authentication adapter type token
     *
     * @return string
     */
    public static function getType();
}

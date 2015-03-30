<?php

/**
 * \AppserverIo\Http\Authentication\AuthenticationInterface
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
 * Interface AuthenticationInterface
 *
 * @author    Johann Zelger <jz@appserver.io>1
 * @author    Bernahrd Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
interface AuthenticationInterface
{

    /**
     * Constructs the authentication type
     *
     * @param array $configData The configuration data for auth type instance
     */
    public function __construct(array $configData = array());

    /**
     * Initialise by the auth content got from client
     *
     * @param string $rawAuthData The content of authentication data sent by client
     * @param string $reqMethod   The https request method as string
     *
     * @return void
     */
    public function init($rawAuthData, $reqMethod);

    /**
     * Try to authenticate
     *
     * @return bool If auth was successful return true if no false will be returned
     */
    public function authenticate();

    /**
     * Returns the authentication type token to compare with request header
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the authentication header for response to set
     *
     * @return string
     */
    public function getAuthenticateHeader();

    /**
     * Returns the parsed username
     *
     * @return string
     */
    public function getUsername();
}

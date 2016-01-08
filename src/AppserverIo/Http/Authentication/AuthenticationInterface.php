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

use AppserverIo\Psr\HttpMessage\RequestInterface;
use AppserverIo\Psr\HttpMessage\ResponseInterface;

/**
 * Interface for specific authentication type implementations.
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @author    Bernahrd Wick <bw@appserver.io>
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
interface AuthenticationInterface
{

    /**
     * Initialize by the authentication type with the data from the request.
     *
     * @param \AppserverIo\Psr\HttpMessage\RequestInterface  $request  The request with the content of authentication data sent by client
     * @param \AppserverIo\Psr\HttpMessage\ResponseInterface $response The response sent back to the client
     *
     * @return void
     * @throws \AppserverIo\Http\Authentication\AuthenticationException If the authentication type can't be initialized
     */
    public function init(RequestInterface $request, ResponseInterface $response);

    /**
     * Try to authenticate against the configured adapter.
     *
     * @param \AppserverIo\Psr\HttpMessage\ResponseInterface $response The response sent back to the client
     *
     * @return void
     * @throws \AppserverIo\Http\Authentication\AuthenticationException Is thrown if the request can't be authenticated
     */
    public function authenticate(ResponseInterface $response);

    /**
     * Returns the authentication type token to compare with request header.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the authentication header for response to set.
     *
     * @return string
     */
    public function getAuthenticateHeader();

    /**
     * Returns the parsed username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Add's a new adapter type to this authentication type.
     *
     * @param string $adapterType The supported adapter type
     *
     * @return void
     */
    public function addSupportedAdapter($adapterType);

    /**
     * Whether or not the adapter is supported with a this authentication type
     *
     * @param string $adapterType The adapter type
     *
     * @return boolean
     */
    public function isAdapterSupported($adapterType);
}

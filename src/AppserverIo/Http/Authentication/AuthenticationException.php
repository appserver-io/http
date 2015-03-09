<?php

/**
 * \AppserverIo\Http\Authentication\AuthenticationException
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
 * Class AuthenticationException
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @author    Bernahrd Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
class AuthenticationException extends \Exception
{

    /**
     * Defines message for invalid or missing min auth configuration
     *
     * @var string
     */
    const MESSAGE_AUTH_DATA_MISSING = 'Missing authentication data from client, check initialization.';

    /**
     * Defines message for invalid or missing adapter options
     *
     * @var string
     */
    const MESSAGE_INVALID_ADAPTER_OPTION = 'Missing or invalid "%s" option for %s authentication adapter.';

    /**
     * Defines message for invalid or missing min auth configuration
     *
     * @var string
     */
    const MESSAGE_MIN_CONFIG_MISSING = 'Missing required configuration values for authentication "%s", needs at least "realm".';

    /**
     * Default response code of authentication exceptions
     *
     * @var int
     */
    protected $code = 401;
}

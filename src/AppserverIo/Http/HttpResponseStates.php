<?php

/**
 * AppserverIo\Http\HttpResponseStates
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
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */

namespace AppserverIo\Http;

/**
 * Class HttpResponseStates
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */
class HttpResponseStates
{

    /**
     * Represent's the state of being initiated with default settings
     *
     * @var int
     */
    const INITIAL = 1;

    /**
     * Represent's the state of being modified by a module
     *
     * @var int
     */
    const MODIFIED = 2;

    /**
     * Represent's the state of being ready to dispatch and stop other modules to process on
     *
     * @var int
     */
    const DISPATCH = 3;
}

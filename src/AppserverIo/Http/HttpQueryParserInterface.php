<?php

/**
 * \AppserverIo\Http\HttpQueryParserInterface
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
 * A query parser interface
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      https://www.appserver.io
 */
interface HttpQueryParserInterface
{

    /**
     * Returns parsed result array
     *
     * @return array
     */
    public function getResult();

    /**
     * Parses the given queryStr and returns result array
     *
     * @param string $queryStr The query string
     *
     * @return array The parsed result as array
     */
    public function parseStr($queryStr);

    /**
     * Parses key value and returns result array
     *
     * @param string $param The param to be parsed
     * @param string $value The value to be set
     *
     * @return void
     */
    public function parseKeyValue($param, $value);
}

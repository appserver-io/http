<?php

/**
 * TechDivision\Http\HttpQueryParserInterface
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */

namespace TechDivision\Http;

/**
 * A query parser interface
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
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

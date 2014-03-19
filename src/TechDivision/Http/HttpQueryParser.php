<?php

/**
 * TechDivision\Http\HttpQueryParser
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
 * A http query parser to parse post and get params to array from query string
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
class HttpQueryParser implements HttpQueryParserInterface
{

    /**
     * Holds the parsed result as array
     *
     * @var array
     */
    protected $result = array();

    /**
     * Holds auto generated indices when ?value[]= is used
     *
     * @var array
     */
    protected $indexCounter = array();
    
    /**
     * The array with content types where the content has to be parsed.
     * 
     * @var array
     */
    protected $parsingRelevantContentTypes = array(
        'application/x-www-form-urlencoded',
        'multipart/form-data'
    );

    /**
     * Returns TRUE if the request is a multipart from data request with
     * content type <code>application/x-www-form-urlencoded</code> or 
     * <code>multipart/form-data</code>.
     * 
     * It is not necessary to use in_array() because the content type can be 
     * extended by encoding, e. g. application/x-www-form-urlencoded; UTF-8.
     *
     * @param string|null $contentType The content's type
     *
     * @return boolean TRUE if the content has to be parsed, else FALSE
     */
    public function isParsingRelevant($contentType)
    {
        foreach ($this->parsingRelevantContentTypes as $relevantContentType) {
            if (strpos($contentType, $relevantContentType) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Internal recursive array merge functionality, due to the array_merge_recursive core php function
     * handles array indices on its own, so its not possible to set it manually while merging.
     *
     * @param array &$array   The existing array reference to merge another array in.
     * @param array &$array_i The array reference which should be merged into a existing one.
     *
     * @return void
     */
    protected function merge(&$array, &$array_i)
    {
        // for each element of the array (key => value)
        foreach ($array_i as $k => $v) {
            // if the value itself is an array
            if (is_array($v)) {
                if (!isset($array[$k])) {
                    $array[$k] = array();
                }
                // the process repeats recursively
                $this->merge($array[$k], $v);
            // else, the value is assigned to the current element of the resulting array
            } else {
                if (isset($array[$k]) && is_array($array[$k])) {
                    $array[$k][0] = $v;
                } else {
                    $array[$k] = $v;
                }
            }
        }
    }

    /**
     * Prepares query string and returns it
     *
     * @param string $queryStr The unprepared query string
     *
     * @return string The prepared query string
     */
    public function prepareQueryStr($queryStr)
    {
        // decode query string
        $queryStr = urldecode($queryStr);
        // cut off '?' if its at the beginning of given query string
        if (strpos($queryStr, '?') !== false) {
            $queryStr = substr($queryStr, 1, strlen($queryStr));
        }
        // return prepared query string
        return $queryStr;
    }

    /**
     * Returns parsed result array
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Parses the given queryStr and returns result array
     *
     * @param string $queryStr The query string given by request
     *
     * @return array The parsed result as array
     */
    public function parseStr($queryStr)
    {
        // prepare query string before parsing it
        $queryStr = $this->prepareQueryStr($queryStr);
        // check if query string is empty
        if (empty($queryStr) === false) {
            // fetch pairs from query
            $pairs = explode('&', $this->prepareQueryStr($queryStr));
            // iterate over all pairs1
            foreach ($pairs as $pair) {
                // split pair into param and value
                list($param, $value) = explode('=', $pair);
                // parse key value
                $this->parseKeyValue($param, $value);
            }
        }
        // return new merged result
        return $this->result;
    }

    /**
     * Parses key value and returns result array
     *
     * @param string $param The param to be parsed
     * @param string $value The value to be set
     *
     * @return void
     */
    public function parseKeyValue($param, $value)
    {
        // set default buildValue if no array structure is given
        $buildValue = array($param => $value);
        // grab array structure if its given
        preg_match_all('/\[([^]]*)\]/s', $param, $arrayKeys);
        // check if there was array structure found
        if (count($arrayKeys[1])) {
            // get main key from param string
            $mainKey = stristr($param, '[', true);
            // set main key on top to be part of array structure
            array_unshift($arrayKeys[1], $mainKey);
            // sort the other way round.
            krsort($arrayKeys[1]);
            // init build value which can be either the value at the beginning or a
            // array struct while iterate all keys
            $buildValue = $value;
            // init index counter hash var
            $arrayIndexCounterHash = $param;
            $parentArrayKey = '';
            // iterate all found array keys
            foreach ($arrayKeys[1] as $arrayIndex => $arrayKey) {
                // init a tmp array
                $tmpArray = array();
                // check if dynamic index should be generated
                if ($arrayKey != '') {
                    // if not, just set build value set before
                    $tmpArray[$arrayKey] = $buildValue;
                    // check if arrayKey is int to preset auto index value
                    if (is_numeric($arrayKey)) {
                        $presetIndexHash = substr($arrayIndexCounterHash, 0, -(strlen($arrayKey)+2)) . '[]';
                        if (isset($this->indexCounter[$presetIndexHash])) {
                            if ((int)$this->indexCounter[$presetIndexHash] < (int)$arrayKey) {
                                $this->indexCounter[$presetIndexHash] = $arrayKey;
                            }
                        } else {
                            $this->indexCounter[$presetIndexHash] = $arrayKey;
                        }
                    }
                } else {
                    // check if index counter has already registered
                    if (isset($this->indexCounter[$arrayIndexCounterHash])) {
                        // increase auto generated index for this array hash
                        $this->indexCounter[$arrayIndexCounterHash]++;
                    } else {
                        // if not check if there is a existing one begin at index 0
                        $this->indexCounter[$arrayIndexCounterHash] = 0;
                    }
                    // build up tmp array with auto generated index
                    $tmpArray[$this->indexCounter[$arrayIndexCounterHash]] = $buildValue;
                }
                // set temp array to as new buildValue for next iteration
                $buildValue = $tmpArray;
                // remove key from index counter hash
                $arrayIndexCounterHash = substr($arrayIndexCounterHash, 0, -(strlen($arrayKey)+2));
            }
        }
        // merge buildValue into existing result
        $this->merge($this->result, $buildValue);
    }

    /**
     * Clears the result
     *
     * @return void
     */
    public function clear()
    {
        $this->result = array();
    }
}

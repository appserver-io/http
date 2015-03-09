<?php

/**
 * \AppserverIo\Http\Authentication\DigestAuthentication
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
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Http\Authentication;

use AppserverIo\Http\Authentication\Adapters\HtdigestAdapter;

/**
 * Class DigestAuthentication
 *
 * @author    Johann Zelger <jz@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
class DigestAuthentication extends AbstractAuthentication
{

    /**
     * Defines the default authentication adapter used if none was specified
     *
     * @var string
     */
    const DEFAULT_ADAPTER = HtdigestAdapter::ADAPTER_TYPE;

    /**
     * Defines the auth type which should match the client request type definition
     *
     * @var string
     */
    const AUTH_TYPE = 'Digest';

    /**
     * Parses the header content set in init before
     *
     * @param string $rawAuthData The raw authentication data coming from the client
     *
     * @return boolean If parsing was successful
     */
    protected function parse($rawAuthData)
    {
        // init data var
        $data = array();

        // define required data
        $requiredData = array(
            'realm' => 1,
            'nonce' => 1,
            'nc' => 1,
            'cnonce' => 1,
            'qop' => 1,
            'username' => 1,
            'uri' => 1,
            'response' => 1
        );

        // prepare key for parsing logic
        $key = implode('|', array_keys($requiredData));

        // parse header value
        preg_match_all('@(' . $key . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $rawAuthData, $matches, PREG_SET_ORDER);

        // iterate all found values for header value
        foreach ($matches as $match) {
            // check if match could be found
            if ($match[3]) {
                $data[$match[1]] = $match[3];
            } else {
                $data[$match[1]] = $match[4];
            }

            // unset required value because we got it processed
            unset($requiredData[$match[1]]);
        }

        // set if all required data was processed
        $data['method'] = $this->getRequestMethod();
        $this->authData = $requiredData ? false : $data;
    }

    /**
     * Returns the authentication header for response to set
     *
     * @return string
     */
    public function getAuthenticateHeader()
    {
        return $this->getType() . ' realm="' . $this->configData["realm"] . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->configData["realm"]) . '"';
    }
}

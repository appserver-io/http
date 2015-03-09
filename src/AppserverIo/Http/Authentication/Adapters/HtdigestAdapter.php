<?php

/**
 * \AppserverIo\Http\Authentication\Adapters\HtdigestAdapter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Florian Sydekum <fs@techdivision.com>
 * @author    Philipp Dittert <pd@techdivision.com>
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Http\Authentication\Adapters;

use AppserverIo\Http\Authentication\AuthenticationException;
use AppserverIo\Http\Authentication\DigestAuthentication;

/**
 * Authentication adapter for htdigest files
 *
 * @author    Florian Sydekum <fs@techdivision.com>
 * @author    Philipp Dittert <pd@techdivision.com>
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/http
 * @link      http://www.appserver.io
 */
class HtdigestAdapter extends AbstractAdapter
{

    /**
     * Defines the adapter type for this specific adapter
     *
     * @var string
     */
    const ADAPTER_TYPE = 'htdigest';

    /**
     * Authentication methods this adapter is compatible to
     *
     * @var array $usableFor
     */
    protected static $usableFor = array(DigestAuthentication::AUTH_TYPE);

    /**
     * Initializes the adapter.
     *
     * @return void
     *
     * @throws \AppserverIo\Http\Authentication\AuthenticationException
     */
    public function init()
    {

        // check if we got a file at our hands
        $config = $this->getConfig();
        if (!isset($config['file']) || !is_readable($config['file'])) {
            throw new AuthenticationException(
                sprintf(
                    AuthenticationException::MESSAGE_INVALID_ADAPTER_OPTION,
                    'file',
                    $this->getType()
                )
            );
        }

        // get content of htdigest file
        $htDigestData = file($config['file']);

        // prepare htdigest entries
        foreach ($htDigestData as $entry) {
            list($user, $realm, $hash) = explode(':', $entry);
            $this->credentials[$user] = array('user'=>$user, 'realm'=>$realm, 'hash'=>trim($hash));
        }
    }

    /**
     * Authenticates a user/realm/H1 hash combination.
     *
     * @param array $authData The data used to authenticate against our credentials
     *
     * @return boolean TRUE if authentication was successful, else FALSE
     */
    public function authenticate(array $authData)
    {

        // if user is valid
        $credentials = $this->getCredentials();
        $user = $authData['username'];
        if ($credentials[$user] && $credentials[$user]['realm'] == $authData['realm']) {
            $HA1 = $credentials[$user]['hash'];
            $HA2 = md5($authData['method'].":".$authData['uri']);
            $middle = ':'.$authData['nonce'].':'.$authData['nc'].':'.$authData['cnonce'].':'.$authData['qop'].':';
            $response = md5($HA1.$middle.$HA2);

            if ($authData['response'] == $response) {
                return true;
            }
        }
        return false;
    }
}

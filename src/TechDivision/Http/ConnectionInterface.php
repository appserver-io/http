<?php
/**
 * \TechDivision\Http\ConnectionInterface
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace TechDivision\Http;

/**
 * Interface ConnectionInterface
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ConnectionInterface
{

    /**
     * Processes the request got from the connected client in a proper way the given
     * protocol type and version expects.
     *
     * @return void
     */
    public function negotiate();

    /**
     * Return's the socket implementation
     *
     * @return \TechDivision\WebServer\Sockets\SocketInterface
     */
    public function getSocket();
}


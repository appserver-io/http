<?php

namespace TechDivision\Http;

use TechDivision\Socket\SocketInterface;

interface ConnectionInterface
{

    /**
     * The connection needs a socket implementation to handle the connection.
     *
     * @param \TechDivision\Socket\SocketInterface $socket The socket implementation to use for
     *                                                          connection handling.
     */
    public function __construct(SocketInterface $socket);

    /**
     * Negotiates the connection with the connected client in a proper way the given
     * protocol type and version expects.
     *
     * @return string The buffer
     */
    public function negotiate();

    /**
     * Return's the socket implementation
     *
     * @return \TechDivision\Socket\SocketInterface
     */
    public function getSocket();

}


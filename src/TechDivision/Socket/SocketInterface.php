<?php

namespace TechDivision\Socket;

interface SocketInterface
{

    /**
     * Creates a stream socket server and returns a instance of Stream implementation with server socket in it.
     *
     * @param string $address The address the server should be listening to. For example 0.0.0.0:8080
     *
     * @return \TechDivision\Socket\SocketInterface The Stream instance with a server socket created.
     */
    public function getServerInstance($address);

    /**
     * Return's an instance of Stream with preset resource in it.
     *
     * @param resource $socket The socket resource to use
     * @return \TechDivision\Socket\SocketInterface
     */
    public function getInstance($socket);

    /**
     * Accepts connections from clients and build up a instance of Stream with connection resource in it.
     *
     * @param int $acceptTimeout  The timeout in seconds to wait for accepting connections.
     * @param int $receiveTimeout The timeout in seconds to wait for read a line.
     *
     * @return \TechDivision\Socket\SocketInterface The Stream instance with the connection socket accepted.
     */
    public function accept($acceptTimeout = 600, $receiveTimeout = 60);

    /**
     * Return's the line read from connection resource
     *
     * @param int $readLength     The max length to read for a line.
     *
     * @return string;
     */
    public function readLine($readLength = 256);

    /**
     * Writes the given message to the connection resource.
     *
     * @param string $message The message to write to the connection resource.
     *
     * @return int
     */
    public function write($message);

    /**
     * Closes the connection resource
     *
     * @return bool
     */
    public function close();

    /**
     * Set's the connection resource
     *
     * @param mixed $socket
     */
    public function setSocket($socket);

    /**
     * Return's the connection resource
     *
     * @return mixed
     */
    public function getSocket();
}


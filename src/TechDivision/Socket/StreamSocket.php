<?php

namespace TechDivision\Socket;

class StreamSocket implements SocketInterface
{

    /**
     * Holds the connection resource itselfe.
     *
     * @var resource
     */
    public $socket;

    /**
     * Creates a stream socket server and returns a instance of Stream implementation with server socket in it.
     *
     * @param string $address The address the server should be listening to. For example 0.0.0.0:8080
     *
     * @return \TechDivision\Socket\Stream The Stream instance with a server socket created.
     */
    public function getServerInstance($address)
    {
        $serverSocket = stream_socket_server(
            'tcp://' . $address,
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        // set blocking mode
        stream_set_blocking($serverSocket, 1);
        // create instance and return it.
        return $this->getInstance($serverSocket);
    }

    /**
     * Return's an instance of Stream with preset resource in it.
     *
     * @param resource $socket The socket resource to use
     * @return \TechDivision\Socket\Stream
     */
    public function getInstance($socket)
    {
        $connection = new self();
        $connection->setSocket($socket);
        return $connection;
    }

    /**
     * Accepts connections from clients and build up a instance of Stream with connection resource in it.
     *
     * @param int $acceptTimeout  The timeout in seconds to wait for accepting connections.
     * @param int $receiveTimeout The timeout in seconds to wait for read a line.
     *
     * @return \TechDivision\Socket\Stream The Stream instance with the connection socket accepted.
     */
    public function accept($acceptTimeout = 120, $receiveTimeout = 10)
    {
        $connectionResource = stream_socket_accept($this->getSocket(), $acceptTimeout);
        // set timeout for read data fom client
        stream_set_timeout($connectionResource, $receiveTimeout);
        return $this->getInstance($connectionResource);

    }

    /**
     * Return's the line read from connection resource
     *
     * @param int $readLength The max length to read for a line.
     *
     * @return string;
     */
    public function readLine($readLength = 256, $receiveTimeout = null)
    {
        if ($receiveTimeout) {
            // set timeout for read data fom client
            stream_set_timeout($this->getSocket(), $receiveTimeout);
        }
        return fgets($this->getSocket(), $readLength);
    }

    /**
     * Writes the given message to the connection resource.
     *
     * @param string $message The message to write to the connection resource.
     *
     * @return int
     */
    public function write($message)
    {
        return fwrite($this->getSocket(), $message, strlen($message));
    }

    /**
     * Closes the connection resource
     *
     * @return bool
     */
    public function close()
    {
        fclose($this->getSocket());
    }

    /**
     * Set's the connection resource
     *
     * @param mixed $socket
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    /**
     * Return's the connection resource
     *
     * @return mixed
     */
    public function getSocket()
    {
        return $this->socket;
    }

}


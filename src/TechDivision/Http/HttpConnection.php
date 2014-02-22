<?php

namespace TechDivision\Http;

use TechDivision\Socket\SocketInterface;
use TechDivision\Http\RequestInterface;
use TechDivision\Http\ConnectionException;

class HttpConnection implements ConnectionInterface
{

    /**
     * Holds the socket implementation to use for connection handling.
     *
     * @var \TechDivision\Socket\SocketInterface
     */
    public $socket;

    /**
     * The connection needs a socket implementation to handle the connection.
     *
     * @param \TechDivision\Socket\SocketInterface $socket The socket implementation to use for
     *                                                          connection handling.
     */
    public function __construct(SocketInterface $socket)
    {
        $this->socket = $socket;
    }

    /**
     * Return's the socket implementation
     *
     * @return \TechDivision\Socket\SocketInterface
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Negotiates the connection with the connected client in a proper way the given
     * protocol type and version expects. The result will be a request instance if all data was valid.
     *
     * @return \TechDivision\Http\RequestInterface The request instance
     * @throws \TechDivision\Http\ConnectionException
     */
    public function negotiate()
    {
        try {
            // read first line from connection socket
            $line = $this->getSocket()->readLine();

            /**
             * In the interest of robustness, servers SHOULD ignore any empty
             * line(s) received where a Request-Line is expected. In other words, if
             * the server is reading the protocol stream at the beginning of a
             * message and receives a CRLF first, it should ignore the CRLF.
             *
             * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.1
             */
            if ($line === "\r\n") {
                // ignore the first CRLF and go on reading the expected start-line.
                $line = $this->getSocket()->readLine();
            }
            // check if timeout occured
            if (strlen($line) === 0) {
                throw new ConnectionException('Timeout reached on read.');
            }
            // validate and parse read line
            if (!preg_match(
                    "/(OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)\s(.*)\s(HTTP\/1\.0|HTTP\/1\.1)/",
                    $line,
                    $matches
                )
            ) {
                throw new ConnectionException('Bad request.');
            }
            // grab http version and request method from first request line.
            list($requestLine, $requestMethod, $requestUri, $protocolVersion) = $matches;

            /**
             * Parse headers in a proper way
             *
             * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
             */
            $lineCounter = 0;
            while($line != "\r\n") {
                // read next line
                $line = $this->getSocket()->readLine();
                // check if read timeout occured
                if (strlen($line) === 0) {
                    throw new ConnectionException('Timeout reached on read.');
                }
                // check if first line is a CRLF
                if (($lineCounter === 0) && ($line === "\r\n")) {
                    throw new ConnectionException('Missing headers.');
                }
                // extract header info
                $extractedHeaderInfo = explode(':', trim(strtolower($line)));
                if (!$extractedHeaderInfo) {
                    throw new ConnectionException('Wrong header format.');
                }
                list($headerName, $headerValue) = $extractedHeaderInfo;
                // collect headers
                $headers[$headerName] = $headerValue;
                // pre inc counter
                ++$lineCounter;
            }

            // check if message body will be transmitted
            if (array_key_exists('transfer-encoding', $headers) || array_key_exists('content', $headers)) {

            }


        } catch (ConnectionException $e) {
            $this->getSocket()->write($e->getMessage() . PHP_EOL);
            $this->getSocket()->close();
        }
    }
}

<?php
/**
 * \TechDivision\Http\HttpConnection
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

use TechDivision\WebServer\Interfaces\ConnectionHandlerInterface;
use TechDivision\WebServer\Interfaces\ServerConfigurationInterface;
use TechDivision\WebServer\Interfaces\ServerContextInterface;
use TechDivision\WebServer\Sockets\SocketInterface;

use TechDivision\WebServer\Modules\CoreModule;
use TechDivision\WebServer\Modules\DirectoryModule;

use TechDivision\Http\HttpRequestInterface;
use TechDivision\Http\HttpParserInterface;
use TechDivision\Http\HttpProtocol;
use TechDivision\Http\HttpRequest;
use TechDivision\Http\HttpResponse;

/**
 * Class HttpConnectionHandler
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
class HttpConnectionHandler implements ConnectionHandlerInterface
{

    /**
     * Hold's parser instance
     *
     * @var \TechDivision\Http\HttpParserInterface
     */
    protected $parser;

    /**
     * Hold's the server context instance
     *
     * @var \TechDivision\WebServer\Interfaces\ServerContextInterface
     */
    protected $serverContext;

    /**
     * Inits the connection handler
     *
     * @param \TechDivision\WebServer\Interfaces\ServerContextInterface $serverContext The server's context
     *
     * @return void
     */
    public function init(ServerContextInterface $serverContext)
    {
        // set server context
        $this->serverContext = $serverContext;

        // init http request object
        $httpRequest = new HttpRequest();
        // get initial documentRoot from server context
        $httpRequest->setDocumentRoot($this->getServerConfig()->getDocumentRoot());

        // init http response object
        $httpResponse = new HttpResponse();
        // set default server signature
        $httpResponse->setServerSignature($this->getServerConfig()->getSignature());

        // setup http parser
        $this->parser = new HttpParser($httpRequest, $httpResponse);
    }

    /**
     * Return's the server context instance
     *
     * @return \TechDivision\WebServer\Interfaces\ServerContextInterface
     */
    public function getServerContext()
    {
        return $this->serverContext;
    }

    /**
     * Return's the server's configuration
     *
     * @return \TechDivision\WebServer\Interfaces\ServerConfigurationInterface
     */
    public function getServerConfig()
    {
        return $this->getServerContext()->getServerConfig();
    }

    /**
     * Return's the parser instance
     *
     * @return \TechDivision\Http\HttpParserInterface
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Handles the connection with the connected client in a proper way the given
     * protocol type and version expects for example.
     *
     * @param \TechDivision\WebServer\Sockets\SocketInterface $connection The connection to handle
     *
     * @return bool Weather it was responsible to handle the firstLine or not.
     */
    public function handle(SocketInterface $connection)
    {
        // try to handle request if its a http request
        try {
            // get instances for short calls
            $parser = $this->getParser();
            //$connection = $this->getServerContext()->getConnectionPool()->get($connectionId);

            // reset request and response
            $parser->getRequest()->init();
            $parser->getResponse()->init();

            // set first line from connection
            $line = $connection->readLine();

            /**
             * In the interest of robustness, servers SHOULD ignore any empty
             * line(s) received where a Request-Line is expected. In other words, if
             * the server is reading the protocol stream at the beginning of a
             * message and receives a CRLF first, it should ignore the CRLF.
             *
             * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.1
             */
            if ($line === "\r\n") {
                // ignore the first CRLF and go on reading the expected start-line.
                $line = $connection->readLine();
            }
            // parse read line
            $parser->parseStartLine($line);

            /**
             * Parse headers in a proper way
             *
             * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
             */
            $messageHeaders = '';
            while ($line != "\r\n") {
                // read next line
                $line = $connection->readLine();
                // enhance headers
                $messageHeaders .= $line;
            }

            // parse headers
            $parser->parseHeaders($messageHeaders);

            // check if message body will be transmitted
            if ($parser->getRequest()->hasHeader(HttpProtocol::HEADER_CONTENT_LENGTH)) {
                // get content-length header
                $contentLength = (int)$parser->getRequest()->getHeader(HttpProtocol::HEADER_CONTENT_LENGTH);
                // read content until given content-length
                while (ftell($parser->getRequest()->getBodyStream()) < $contentLength) {
                    // read next line
                    $line = $connection->readLine();
                    // enhance body with new line
                    fwrite($parser->getRequest()->getBodyStream(), $line, strlen($line));
                }
                // set pointer offset to zero
                // fseek($parser->getRequest()->getBodyStream(), 0);
            }

            // process modules
            foreach ($this->getServerContext()->getModules() as $module) {
                $module->process($parser->getRequest(), $parser->getResponse());
            }

            // write response status-line
            $connection->write($parser->getResponse()->getStatusLine());
            // write response headers
            $connection->write($parser->getResponse()->getHeaderString());
            // stream response body to connection
            $connection->copyStream($parser->getResponse()->getBodyStream());

            // close connection todo: implement keep-alive
            $connection->close();

            return true;

        } catch (\Exception $e) {
            $connection->write($e->getMessage() . PHP_EOL);
            $connection->close();
        }
    }
}

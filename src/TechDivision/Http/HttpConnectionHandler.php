<?php
/**
 * \TechDivision\Http\HttpConnection
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

use TechDivision\WebServer\Dictionaries\ServerVars;
use TechDivision\WebServer\Exceptions\ModuleException;
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
        // set server software per default
        $httpResponse->addHeader(
            HttpProtocol::HEADER_SERVER,
            $serverContext->getServerVar(ServerVars::SERVER_SOFTWARE)
        );

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
        // get instances for short calls
        $parser = $this->getParser();
        $queryParser = $parser->getQueryParser();
        $request = $parser->getRequest();
        $response = $parser->getResponse();

        // try to handle request if its a http request
        try {
            // reset request and response
            $request->init();
            $response->init();

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
            if ($request->hasHeader(HttpProtocol::HEADER_CONTENT_LENGTH)) {
                // get content-length header
                $contentLength = (int)$request->getHeader(HttpProtocol::HEADER_CONTENT_LENGTH);
                // copy connection stream to body stream by given content length
                $request->copyBodyStream($connection->getConnectionResource(), $contentLength);
                // get content out for oldschool query parsing todo: refactor query parsing
                $content = $request->getBodyContent();

                // check if request has to be parsed depending on Content-Type header
                if ($queryParser->isParsingRelevant($request->getHeader(HttpProtocol::HEADER_CONTENT_TYPE))) {
                    // checks if request has multipart formdata or not
                    preg_match('/boundary=(.*)$/', $request->getHeader(HttpProtocol::HEADER_CONTENT_TYPE), $boundaryMatches);
                    // check if boundaryMatches are found
                    // todo: refactor content string var to be able to use bodyStream
                    if (count($boundaryMatches) > 0) {
                        $parser->parseMultipartFormData($content);
                    } else {
                        $queryParser->parseStr(urldecode($content));
                    }
                }
            }

            // set parsed query and multipart form params to request
            $request->setParams($queryParser->getResult());

            // init connection & protocol server vars
            $this->initServerVars();

            // process modules
            foreach ($this->getServerContext()->getModules() as $module) {
                $module->process($request, $response);
                // check if response should be dispatched now and stop other modules to process
                if ($response->hasState(HttpResponseStates::DISPATCH)) {
                    break;
                }
            }

        } catch (\Exception $e) {
            $response->setStatusCode(500);
            $response->appendBodyStream($e->getMessage());
        }

        // write response status-line
        $connection->write($response->getStatusLine());
        // write response headers
        $connection->write($response->getHeaderString());
        // stream response body to connection
        $connection->copyStream($response->getBodyStream());

        // close connection todo: implement keep-alive
        $connection->close();
    }

    /**
     * Init's the server vars by parsed request
     *
     * @return void
     */
    public function initServerVars()
    {
        // get server context to local var reference
        $serverContext = $this->getServerContext();
        // get request to local var reference
        $request = $this->getParser()->getRequest();

        // get document root to local var to avoid calling function everytime needed
        $documentRoot = $serverContext->getServerVar(ServerVars::DOCUMENT_ROOT);

        // set http protocol because this is the http connection class which implements http 1.1
        $serverContext->setServerVar(ServerVars::SERVER_PROTOCOL, 'HTTP/1.1');

        // set server vars by request
        $serverContext->setServerVar(
            ServerVars::HTTP_USER_AGENT,
            $request->getHeader(HttpProtocol::HEADER_USER_AGENT)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_REFERER,
            $request->getHeader(HttpProtocol::HEADER_REFERER)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_COOKIE,
            $request->getHeader(HttpProtocol::HEADER_COOKIE)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_HOST,
            $request->getHeader(HttpProtocol::HEADER_HOST)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_HEADER_X_REQUESTED_WITH,
            $request->getHeader(HttpProtocol::HEADER_X_REQUESTED_WITH)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_ACCEPT,
            $request->getHeader(HttpProtocol::HEADER_ACCEPT)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_ACCEPT_CHARSET,
            $request->getHeader(HttpProtocol::HEADER_ACCEPT_CHARSET)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_ACCEPT_ENCODING,
            $request->getHeader(HttpProtocol::HEADER_ACCEPT_ENCODING)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_ACCEPT_LANGUAGE,
            $request->getHeader(HttpProtocol::HEADER_ACCEPT_LANGUAGE)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_CONNECTION,
            $request->getHeader(HttpProtocol::HEADER_CONNECTION)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_FORWARDED,
            $request->getHeader(HttpProtocol::HEADER_X_FORWARD)
        );
        $serverContext->setServerVar(
            ServerVars::HTTP_PROXY_CONNECTION,
            $request->getHeader(HttpProtocol::HEADER_PROXY_CONNECTION)
        );
        $serverContext->setServerVar(
            ServerVars::REQUEST_METHOD,
            $request->getMethod()
        );
        $serverContext->setServerVar(
            ServerVars::QUERY_STRING,
            $request->getQueryString()
        );
        $serverContext->setServerVar(
            ServerVars::REQUEST_URI,
            $request->getUri()
        );
        // check if script name exists
        if ($request->getScriptName()) {
            // set server vars for script
            $serverContext->setServerVar(
                ServerVars::SCRIPT_NAME,
                $request->getScriptName()
            );
            $serverContext->setServerVar(
                ServerVars::SCRIPT_FILENAME,
                $documentRoot . $request->getScriptName()
            );
        }
        // check if path info is given
        if ($request->getPathInfo()) {
            // set path info to server vars
            $serverContext->setServerVar(
                ServerVars::PATH_INFO,
                $request->getPathInfo()
            );
            // set translated path to server vars
            $serverContext->setServerVar(
                ServerVars::PATH_TRANSLATED,
                $documentRoot . $request->getPathInfo()
            );
        }
        /**
         * Due to we have noe infos about the client we're not able to set all remote server vars yet
         * Todo: map them as soon it's possible to get remote connection infos
         *
         * REMOTE_ADDR
         * REMOTE_HOST
         * REMOTE_PORT
         * REMOTE_USER
         * REMOTE_IDENT
         * REDIRECT_REMOTE_USER
         */

        /**
         * Todo: implement date infos in server vars
         *
         * REQUEST_TIME
         * REQUEST_TIME_FLOAT
         * TIME_YEAR
         * TIME_MON
         * TIME_DAY
         * TIME_HOUR
         * TIME_MIN
         * TIME_SEC
         * TIME_WDAY
         * TIME
         */
    }
}

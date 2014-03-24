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
use TechDivision\WebServer\Interfaces\WorkerInterface;
use TechDivision\WebServer\Sockets\SocketInterface;

use TechDivision\WebServer\Modules\CoreModule;
use TechDivision\WebServer\Modules\DirectoryModule;

use TechDivision\Http\HttpRequestInterface;
use TechDivision\Http\HttpParserInterface;
use TechDivision\Http\HttpProtocol;
use TechDivision\Http\HttpRequest;
use TechDivision\Http\HttpResponse;
use TechDivision\WebServer\Sockets\SocketReadTimeoutException;

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
     * Hold's an array of modules to use for connection handler
     *
     * @var array
     */
    protected $modules;

    /**
     * Hold's errors page template
     *
     * @var string
     */
    protected $errorsPageTemplate;

    /**
     * Hold's the connection instance
     *
     * @var \TechDivision\WebServer\Sockets\SocketInterface
     */
    protected $connection;

    /**
     * Hold's the worker instance
     *
     * @var \TechDivision\WebServer\Interfaces\WorkerInterface
     */
    protected $worker;

    /**
     * Inits the connection handler by given context and params
     *
     * @param \TechDivision\WebServer\Interfaces\ServerContextInterface $serverContext The server's context
     * @param array                                                     $params        The params for connection handler
     *
     * @return void
     */
    public function init(ServerContextInterface $serverContext, array $params = null)
    {
        // set server context
        $this->serverContext = $serverContext;

        // set params
        $this->errorsPageTemplate = $params["errorsPageTemplate"];

        // init http request object
        $httpRequest = new HttpRequest();

        // init http response object
        $httpResponse = new HttpResponse();
        // set default response headers
        $httpResponse->setDefaultHeaders(
            array(
                HttpProtocol::HEADER_SERVER =>  $serverContext->getServerVar(ServerVars::SERVER_SOFTWARE),
                HttpProtocol::HEADER_CONNECTION => HttpProtocol::HEADER_CONNECTION_VALUE_CLOSE
            )
        );

        // setup http parser
        $this->parser = new HttpParser($httpRequest, $httpResponse);

        // register shutdown handler
        register_shutdown_function(array(&$this, "shutdown"));

    }

    /**
     * Injects all needed modules for connection handler to process
     *
     * @param array $modules An array of Modules
     *
     * @return void
     */
    public function injectModules($modules)
    {
        $this->modules = $modules;
    }

    /**
     * Return's all needed modules as array for connection handler to process
     *
     * @return array An array of Modules
     */
    public function getModules()
    {
        return $this->modules;
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
     * Return's the connection used to handle with
     *
     * @return \TechDivision\WebServer\Sockets\SocketInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Return's the worker instance which starte this worker thread
     *
     * @return \TechDivision\WebServer\Interfaces\WorkerInterface
     */
    protected function getWorker()
    {
        return $this->worker;
    }

    /**
     * Return's the template for errors page to render
     *
     * @return string
     */
    public function getErrorsPageTemplate()
    {
        return $this->errorsPageTemplate;
    }

    /**
     * Handles the connection with the connected client in a proper way the given
     * protocol type and version expects for example.
     *
     * @param \TechDivision\WebServer\Sockets\SocketInterface    $connection The connection to handle
     * @param \TechDivision\WebServer\Interfaces\WorkerInterface $worker     The worker how started this handle
     *
     * @return bool Weather it was responsible to handle the firstLine or not.
     */
    public function handle(SocketInterface $connection, WorkerInterface $worker)
    {

        // add connection ref to self
        $this->connection = $connection;
        $this->worker = $worker;

        // get instances for short calls
        $serverContext = $this->getServerContext();
        $serverConfig = $serverContext->getServerConfig();
        $parser = $this->getParser();
        $queryParser = $parser->getQueryParser();
        $request = $parser->getRequest();
        $response = $parser->getResponse();

        // init keep alive settings
        $keepAliveTimeout = (int)$serverConfig->getKeepAliveTimeout();
        $keepAliveMax = (int)$serverConfig->getKeepAliveMax();

        do {
            // try to handle request if its a http request
            try {

                // reset connection infos to server vars
                $serverContext->setConnectionServerVars($connection);

                // time settings
                $serverContext->setServerVar(ServerVars::REQUEST_TIME, time());

                /**
                 * Todo: maybe later on there have to be other time vars too especially for rewrite module.
                 *
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

                // reset request and response
                $request->init();
                $response->init();

                // init keep alive connection flag
                $keepAliveConnection = false;

                // set first line from connection
                $line = $connection->readLine(1024, $keepAliveTimeout);

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
                    $line = $connection->readLine(1024, $keepAliveTimeout);
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

                // process connection type keep-alive
                if (strcasecmp(
                        $request->getHeader(HttpProtocol::HEADER_CONNECTION),
                        HttpProtocol::HEADER_CONNECTION_VALUE_KEEPALIVE
                    ) === 0
                ) {
                    // only if max connections were not reached yet
                    if ($keepAliveMax > 0) {
                        // enable keep alive connection
                        $keepAliveConnection = true;
                        // set keep-alive headers
                        $response->addHeader(HttpProtocol::HEADER_CONNECTION, HttpProtocol::HEADER_CONNECTION_VALUE_KEEPALIVE);
                        $response->addHeader(HttpProtocol::HEADER_KEEP_ALIVE, "timeout: $keepAliveTimeout, max: $keepAliveMax");
                        // decrease keep-alive max
                        --$keepAliveMax;
                    }
                }

                // check if message body will be transmitted
                if ($request->hasHeader(HttpProtocol::HEADER_CONTENT_LENGTH)) {
                    // get content-length header
                    if (($contentLength = (int)$request->getHeader(HttpProtocol::HEADER_CONTENT_LENGTH)) > 0) {
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
                }

                // set parsed query and multipart form params to request
                $request->setParams($queryParser->getResult());

                // init connection & protocol server vars
                $this->initServerVars();

                // process modules
                $modules = $this->getModules();
                foreach ($modules as $module) {
                    $module->process($request, $response);
                    // check if response should be dispatched now and stop other modules to process
                    if ($response->hasState(HttpResponseStates::DISPATCH)) {
                        break;
                    }
                }
            } catch (SocketReadTimeoutException $e) {
                // set request timeout status code
                $response->setStatusCode(408);
                $this->renderErrorPage($e->__toString());

            } catch (\Exception $e) {
                // set status code given by exception
                $response->setStatusCode($e->getCode());
                $this->renderErrorPage($e->__toString());
            }

            // send response to client
            $this->sendResponse();

            // init server vars
            $serverContext->initServerVars();

        } while($keepAliveConnection === true);

        // finally close connection
        $connection->close();
    }

    /**
     * Renders error page by given exception
     *
     * @param string $errorMessage The error message string to render
     *
     * @return void
     */
    public function renderErrorPage($errorMessage)
    {
        // get response ref to local var for template rendering
        $response = $this->getParser()->getResponse();
        // check if template is given and exists
        if (($errorsPageTemplatePath = $this->getServerContext()->getServerVar(ServerVars::SERVER_ERRORS_PAGE_TEMPLATE_PATH))
            && is_file($errorsPageTemplatePath)) {
            // render errors page
            ob_start();
            require $errorsPageTemplatePath;
            $errorsPage = ob_get_clean();
        } else {
            // build up error message manually without template
            $errorsPage = $response->getStatusCode() . ' ' . $response->getStatusReasonPhrase() .
                PHP_EOL . PHP_EOL . $errorMessage .
                PHP_EOL . PHP_EOL . strip_tags($this->getServerContext()->getServerVar(ServerVars::SERVER_SIGNATURE));
        }
        // append errors page to response body
        $response->appendBodyStream($errorsPage);
    }

    /**
     * Send's response to connected client
     *
     * @return void
     */
    public function sendResponse()
    {
        // get local var refs
        $response = $this->getParser()->getResponse();
        $connection = $this->getConnection();

        // write response status-line
        $connection->write($response->getStatusLine());
        // write response headers
        $connection->write($response->getHeaderString());

        // stream response body to connection
        $connection->copyStream($response->getBodyStream());
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

        // set http protocol because this is the http connection class which implements http 1.1
        $serverContext->setServerVar(ServerVars::SERVER_PROTOCOL, 'HTTP/1.1');

        // get http host to set server name var
        $serverName = $request->getHeader(HttpProtocol::HEADER_HOST);
        if (strpos($serverName, ':') !== false) {
            $serverName = strstr($serverName, ':', true);
        }

        // set server name var
        $serverContext->setServerVar(ServerVars::SERVER_NAME, $serverName);

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
    }

    /**
     * Does shutdown logic for worker if something breaks in process
     *
     * @return void
     */
    public function shutdown()
    {
        // get refs to local vars
        $connection = $this->getConnection();
        $worker = $this->getWorker();

        // check if connections is still alive
        if ($connection) {
            // set response code to 500 Internal Server Error
            $this->getParser()->getResponse()->setStatusCode(500);

            // get last error array
            $lastError = error_get_last();

            // check if it was a fatal error
            if (!is_null($lastError) && $lastError['type'] === 1) {
                $errorMessage = 'PHP Fatal error: ' . $lastError['message'] .
                    ' in ' . $lastError['file'] . ' on line ' . $lastError['line'];
                $this->renderErrorPage($errorMessage);
            }

            // send response before shutdown
            $this->sendResponse();

            // close client connection
            $this->getConnection()->close();
        }

        // check if worker is given
        if ($worker) {
            // call shutdown process on worker to respawn
            $this->getWorker()->shutdown();
        }
    }
}

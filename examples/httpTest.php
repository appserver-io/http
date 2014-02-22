<?php

class RequestHandler extends Thread
{
	
	public function __construct($socket) {
		$this->socket = $socket;
	}
	
	public function getResponseContent()
	{
		$filename = '/mnt/hgfs/Downloads' . $this->requestUri;
		
		$this->body = file_get_contents($filename);
		
		return "HTTP/1.1 200 OK" . PHP_EOL .
			   "Content-Type: " . mime_content_type(basename($filename)) . PHP_EOL .
			   "Content-Length: " . strlen($this->body) . PHP_EOL .
			   "Connection: keep-alive" . PHP_EOL .
			   PHP_EOL . $this->body;
	}
	
	public function run() {
		
		// loop endlessly
		for (;;) {
			
			// accept client resource on connect
			$client = stream_socket_accept($this->socket, 30);
				
			// loop until client closed
			while ($client !== null) {
	
				// init vars for further processing
				$buffer = '';
				$headers = array();
					
				
				// set timeout for read data fom client
				stream_set_timeout($client, 10);
				
				
				
				// read request line and validate it
				$line = fgets($client, 2048);
				// check if timeout occured
				if (strlen($line) === 0) {
					fclose($client);
					$client = null;
					continue;
				}			
				if (!preg_match("/(GET|POST|HEAD|PUT|DELETE)\s(.*)\sHTTP\/(1\.0|1\.1)/", $line, $matches)) {
					fwrite($client, 'Bad Request...' . PHP_EOL);
					fclose($client);
				}
				// grab http version and request method from first request line.
				list($requestLine, $requestMethod, $this->requestUri, $httpVersion) = $matches;
				
				// put line to buffer
				$buffer .= $line;
				
				
				/**
				
				// read requested host and validate line
				$line = fgets($client, 2048);
				// check if timeout occured
				if (strlen($line) === 0) {
					fclose($client);
					$client = null;
					// break out of keep-alive loop
					break;
				}
				if (!preg_match("/Host:\s.+/", $line)) {
					fwrite($client, 'Bad Request...' . PHP_EOL);
					fclose($client);
				}
				// put line to buffer
				$buffer .= $line;
				
				echo $buffer . PHP_EOL;
				*/
				
				// read rest of the headers
				while($line != "\r\n") {
					$line = fgets($client, 2048);
					// check if timeout occured
					if (strlen($line) === 0) {
						fclose($client);
						$client = null;
						// break out of keep-alive loop
						break 2;
					}
					$buffer .= $line;
					
					list($headerName, $headerValue) = explode(':', $line);
					$headers[strtolower($headerName)] = trim(strtolower($headerValue));
				}
				
				// read body if header "Content-Length was found"
				if (array_key_exists("content-length", $headers) && (int)$headers["content-length"] > 0) {
					// init vars for further processing
					$body = '';
					$contentLength = (int)$headers["content-length"];
					
					// read content until given content-length
					while(strlen($body) < $contentLength) {
						// read next line
						$line = fgets($client, 2048);
						// check if timeout occured and there is nothing to read
						if (strlen($line) === 0) {
							// break while
							break;
						}
						// enhance body with new line
						$body .= $line;
					}
	
					// check if body was fully read without getting a timeout
					if (strlen($body) < $contentLength) {
						// throw out client
						fclose($client);
						// continue with next accept
						break 2;
					}
				
					// put body to buffer
					$buffer .= $body;
				}
	
				// set read buffer to response body for echoing http server
				$this->body = $buffer;				
				// send http response to client
				fwrite($client, $this->getResponseContent());
				
				// check if connection close header was sent
				if (((array_key_exists("connection", $headers)) && ($headers["connection"] === 'close'))
					|| ((!array_key_exists("connection", $headers)) && ($httpVersion === '1.0'))) {
					// close connection to client
					fclose($client);
					break;
				}
			}
		}
	}
}


// create ssl context
$context = stream_context_create();
stream_context_set_option($context, 'ssl', 'local_cert', 'SSLServer.pem');
stream_context_set_option($context, 'ssl', 'passphrase', 'passphrase');
stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
stream_context_set_option($context, 'ssl', 'verify_peer', false);
// create ssl stream socket server
//$server = stream_socket_server('ssl://0.0.0.0:8084', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
// create stream socket server
$server = stream_socket_server('tcp://0.0.0.0:8080', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);

stream_set_blocking($server, 1);

$threads = array();
	
for ($i = 0; $i < 1024; $i++) {
	$threads[$i] = new RequestHandler($server);
	$threads[$i]->start();
}

// wait for all acceptors
foreach ($threads as $thread) {
	$thread->join();
}
<?php

$serverSocket = stream_socket_server(
    'tcp://0.0.0.0:8080',
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
);
// set blocking mode
stream_set_blocking($serverSocket, 1);

$connectionResource = stream_socket_accept($serverSocket, 600);
// set timeout for read data fom client
stream_set_timeout($connectionResource, 60);

$line = fgets($connectionResource, 256);

// validate and parse read line
if (!preg_match(
    "/(OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)\s(.*)\sHTTP\/(1\.0|1\.1)/",
    $line,
    $matches
)
) {
    $connectionResource->write("404 Bad request." . PHP_EOL);
    $connectionResource->close();
}
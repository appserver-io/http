<?php

// require autoloader. Make sure you did a composer install first.
require_once "../vendor/autoload.php";

$streamSocket = new \TechDivision\Socket\StreamSocket();
$streamSocketServer = $streamSocket->getServerInstance('0.0.0.0:8081');

while($streamConnection = $streamSocketServer->accept()) {
    $httpConnection = new \TechDivision\Http\HttpConnection($streamConnection);
    $httpConnection->negotiate();
}
<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Benchmark\Server;

require dirname(__DIR__) . '/vendor/autoload.php';

// Generic Ratchet websocket server setup
// Custom logic is contained within src/Server.php
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Server()
        )
    ),
    8080
);

$server->run();
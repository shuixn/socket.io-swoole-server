<?php

declare(strict_types=1);

$host = "0.0.0.0";
$port = 9500;
$workerNum = 1;
$daemon = 0;

echo <<<EOT

Examples

Chat Room         http://127.0.0.1:{$port}/index.html

EOT;

$server = new Swoole\Http\Server($host, $port, SWOOLE_BASE);

$server->set([
    'worker_num' => $workerNum,
    'daemonize' => $daemon,
    'document_root' => __DIR__ . '/public',
    'enable_static_handler' => true,
    "static_handler_locations" => ['/', '/public/'],
]);

$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    $response->end("<h1>Hello Friend!</h1>");
});

$server->start();
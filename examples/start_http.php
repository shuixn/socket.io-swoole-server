<?php

declare(strict_types=1);

$server = new Swoole\Http\Server('0.0.0.0', 9500, SWOOLE_BASE);

$server->set([
    'worker_num' => 1,
    'daemonize' => 0,
    'document_root' => __DIR__ . '/public',
    'enable_static_handler' => true,
    "static_handler_locations" => ['/', '/public/'],
]);

$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    $response->end("<h1>Hello Friend!</h1>");
});

$server->start();
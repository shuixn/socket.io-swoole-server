<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

try {

    $config = new SocketIO\Engine\Payload\ConfigPayload();
    $config
        // server worker_num
        ->setWorkerNum(2)
        // server daemonize
        ->setDaemonize(0);

    $io = new SocketIO\Server(9501, $config, function(SocketIO\Server $io) {
        $io->on('connection', function (SocketIO\Server $socket) {
            $socket->on('new message', function (SocketIO\Server $socket) {
                $socket->broadcast('new message', $socket->getMessage());
            });

            $socket->on('new user', function (SocketIO\Server $socket) {
                $socket->broadcast('login', $socket->getMessage());
            });

            $socket->on('disconnect', function (SocketIO\Server $socket) {
                $socket->broadcast('user left', $socket->getMessage());
            });
        });
    });

    $io->start();

} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
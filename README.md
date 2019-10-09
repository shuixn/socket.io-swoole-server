# socket.io-swoole-server
:rainbow: Socket.io Server implementation for Swoole

## Server

```php
$config = new SocketIO\Engine\Server\ConfigPayload();
$config
    // server worker_num
    ->setWorkerNum(1)
    // server daemonize
    ->setDaemonize(0);

$io = new SocketIO\SocketIO(9999, $config);
$io->on('new message', function (SocketIO\SocketIO $socket) {
    $socket->emit('new message', [
        'data' => $socket->getMessage()
    ]);
});

$io->start();
```
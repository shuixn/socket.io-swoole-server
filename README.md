# socket.io-swoole-server
:rainbow: Socket.io Server implementation for Swoole

## Server

### Server Config

```php
$config = new SocketIO\Engine\Server\ConfigPayload();
$config
    // server worker_num
    ->setWorkerNum(2)
    // server daemonize
    ->setDaemonize(0);
```

### Examples

```php
$io = new SocketIO\Server(9501, $config, function(SocketIO\Server $io) {
    $io->on('connection', function (SocketIO\Server $socket) {
        $socket->on('new message', function (SocketIO\Server $socket) {
            $socket->emit('new message', [
                'data' => $socket->getMessage()
            ]);
        });

        $socket->on('new user', function (SocketIO\Server $socket) {
            $socket->broadcast('hello');
        });

        $socket->on('disconnect', function (SocketIO\Server $socket) {
            $socket->broadcast('user left');
        });
    });
});

$io->start();
```
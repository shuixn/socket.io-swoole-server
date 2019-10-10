<?php

declare(strict_types=1);

namespace SocketIO\Engine\WebSocket;

use SocketIO\Engine\Server\ConfigPayload;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Event;
use SocketIO\Parser\Packet;
use SocketIO\Parser\PacketPayload;
use SocketIO\Server as SocketIOServer;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\WebSocket\Frame as WebSocketFrame;
use Swoole\Http\Request as HttpRequest;

/**
 * Class Server
 *
 * @package SocketIO\Engine\WebSocket
 */
class Server
{
    /** @var WebSocketServer */
    protected $server;

    /** @var array */
    protected $serverEvents = [
        'open', 'message', 'close'
    ];

    /** @var array */
    protected $eventPool;

    /**
     * Server constructor.
     *
     * @param int $port
     * @param ConfigPayload $configPayload
     * @param array $eventPool
     */
    public function __construct(int $port, ConfigPayload $configPayload, array $eventPool) {

        $this->eventPool = $eventPool;

        $this->server = new WebSocketServer("0.0.0.0", $port);

        $this->server->set([
            'worker_num' => $configPayload->getWorkerNum() ?? 1,
            'daemonize' => $configPayload->getDaemonize() ?? 0
        ]);

        foreach ($this->serverEvents as $event) {
            $method = 'on' . ucfirst($event);
            if (method_exists($this, $method)) {
                $this->server->on($event, [$this, $method]);
            }
        }

        $this->server->start();
    }

    /**
     * @param WebSocketServer $server
     * @param HttpRequest $request
     */
    public function onOpen(WebSocketServer $server, HttpRequest $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    /**
     * @param WebSocketServer $server
     * @param WebSocketFrame $frame
     */
    public function onMessage(WebSocketServer $server, WebSocketFrame $frame)
    {
        $packetPayload = Packet::decode($frame->data);

        switch ($packetPayload->getType()) {
            case TypeEnum::PING:
                $server->push($frame->fd, TypeEnum::PONG);
                break;
            case TypeEnum::MESSAGE:
                $this->handleEvent($server, $frame, $packetPayload);
                break;
            case TypeEnum::UPGRADE:
                break;
            default:
                $server->push($frame->fd, 'unknown message or wrong packet');
                break;
        }
    }

    /**
     * @param WebSocketServer $server
     * @param int $fd
     */
    public function onClose(WebSocketServer $server, int $fd)
    {
        echo "client {$fd} closed\n";

        /** @var Event $event */
        foreach ($this->eventPool as $event) {
            $event->popListener($fd);
        }
    }

    /**
     * @param WebSocketServer $server
     * @param WebSocketFrame $frame
     * @param PacketPayload $packetPayload
     */
    private function handleEvent(WebSocketServer $server, WebSocketFrame $frame, PacketPayload $packetPayload)
    {
        $namespace = $packetPayload->getNamespace();
        $eventName = $packetPayload->getEvent();

        $isExistEvent = false;

        /** @var Event $event */
        foreach ($this->eventPool as $event) {
            if ($event->getNamespace() == $namespace && $event->getName() == $eventName) {
                $isExistEvent = true;

                $event->pushListener($frame->fd);

                /** @var SocketIOServer $socket */
                $socket = $event->getSocket();
                $socket->setMessage($packetPayload->getMessage());
                $socket->setWebSocketServer($server);
                $socket->setWebSocketFrame($frame);

                $callback = $event->getCallback();

                $callback($socket);
            }
        }

        if (!$isExistEvent) {
            $server->push($frame->fd, 'Bad Event');
        }
    }
}
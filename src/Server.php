<?php

declare(strict_types=1);

namespace SocketIO;

use Co\Channel;
use SocketIO\Engine\Payload\ConfigPayload;
use SocketIO\Engine\Server as EngineServer;
use SocketIO\Enum\Message\PacketTypeEnum;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Event\EventPayload;
use SocketIO\Event\EventPool;
use SocketIO\Storage\Table\EventListenerTable;
use SocketIO\Storage\Table\ListenerEventTable;
use SocketIO\Storage\Table\ListenerTable;
use SocketIO\Parser\WebSocket\Packet;
use SocketIO\Parser\WebSocket\PacketPayload;
use SocketIO\Storage\Table\SessionTable;
use Swoole\WebSocket\Server as WebSocketServer;
use SocketIO\ExceptionHandler\InvalidEventException;

/**
 * Class Server
 *
 * @package SocketIO
 */
class Server
{
    /** @var Callable */
    private $callback;

    /** @var string */
    private $namespace = '/';

    /** @var WebSocketServer */
    private $webSocketServer;

    /** @var int */
    private $fd;

    /** @var string */
    private $message;

    /** @var int */
    private $port;

    /** @var ConfigPayload */
    private $configPayload;

    public function __construct(int $port, ConfigPayload $configPayload, Callable $callback)
    {
        $this->port = $port;

        $this->configPayload = $configPayload;

        $this->callback = $callback;
    }

    /**
     * @return WebSocketServer
     */
    public function getWebSocketServer(): WebSocketServer
    {
        return $this->webSocketServer;
    }

    /**
     * @param WebSocketServer $webSocketServer
     *
     * @return Server
     */
    public function setWebSocketServer(WebSocketServer $webSocketServer): self
    {
        $this->webSocketServer = $webSocketServer;

        return $this;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param int $fd
     * @return Server
     */
    public function setFd(int $fd): Server
    {
        $this->fd = $fd;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return Server
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function of(string $namespace): self
    {
        $this->namespace = !empty($namespace) ? $namespace : $this->namespace;

        return $this;
    }

    /**
     * @param string $eventName
     * @param callable $callback
     *
     * @return Server
     *
     * @throws InvalidEventException
     */
    public function on(string $eventName, callable $callback) : self
    {
        if (empty($eventName) || !is_callable($callback)) {
            throw new InvalidEventException('invalid Event');
        }

        $this->consumeEvent($eventName, $callback);

        return $this;
    }

    private function consumeEvent(string $eventName, callable $callback)
    {
        if (!EventPool::getInstance()->isExist($this->namespace, $eventName)) {
            $chan = new Channel();
            $eventPayload = new EventPayload();
            $eventPayload
                ->setNamespace($this->namespace)
                ->setName($eventName)
                ->setChan($chan);

            EventPool::getInstance()->push($eventPayload);

            go(function () use ($chan, $eventName, $callback) {
                while($data = $chan->pop()) {
                    $webSocketServer = $data['webSocketServer'] ?? null;
                    $fd = $data['fd'] ?? 0;
                    $message = $data['message'] ?? '';

                    $this->setWebSocketServer($webSocketServer);
                    if ($fd != 0) {
                        $this->setFd($fd);
                    }
                    $this->setMessage($message);

                    $callback($this);
                }
            });
        }

        return;
    }

    /**
     * @param string $eventName
     * @param array $data
     */
    public function emit(string $eventName, array $data)
    {
        $packetPayload = new PacketPayload();
        $packetPayload
            ->setNamespace($this->namespace)
            ->setEvent($eventName)
            ->setType(TypeEnum::MESSAGE)
            ->setPacketType(PacketTypeEnum::EVENT)
            ->setMessage(json_encode($data));

        $this->webSocketServer->push($this->fd, Packet::encode($packetPayload));
    }

    /**
     * @param string $data
     * @throws \Exception
     */
    public function broadcast(string $data)
    {
        $listeners = ListenerTable::getInstance()->getListener();
        if (!empty($listeners)) {
            foreach ($listeners as $listener) {
                $this->webSocketServer->push(intval($listener), $data);
            }
        } else {
            $this->webSocketServer->push($this->fd, $data);
        }
    }

    public function start()
    {
        $this->initTables();

        new EngineServer($this->port, $this->configPayload, $this->callback, $this);
    }

    private function initTables()
    {
        SessionTable::getInstance();
        EventListenerTable::getInstance();
        ListenerEventTable::getInstance();
        ListenerTable::getInstance();
    }
}
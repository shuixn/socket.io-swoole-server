<?php

declare(strict_types=1);

namespace SocketIO;

use SocketIO\Engine\Payload\ConfigPayload;
use SocketIO\Engine\Server as EngineServer;
use SocketIO\Enum\Message\PacketTypeEnum;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Event\EventPayload;
use SocketIO\Storage\Table\ListenerTable;
use SocketIO\Parser\WebSocket\Packet;
use SocketIO\Parser\WebSocket\PacketPayload;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\WebSocket\Frame as WebSocketFrame;
use SocketIO\ExceptionHandler\InvalidEventException;

/**
 * Class Server
 *
 * @package SocketIO
 */
class Server
{
    /** @var string */
    private $namespace = '/';

    /** @var WebSocketServer */
    private $webSocketServer;

    /** @var WebSocketFrame */
    private $webSocketFrame;

    /** @var string */
    private $message;

    /** @var int */
    private $port;

    /** @var ConfigPayload */
    private $configPayload;

    /** @var array */
    private $eventPool = [];

    public function __construct(int $port, ConfigPayload $configPayload)
    {
        $this->port = $port;

        $this->configPayload = $configPayload;
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
     * @return WebSocketFrame
     */
    public function getWebSocketFrame(): WebSocketFrame
    {
        return $this->webSocketFrame;
    }

    /**
     * @param WebSocketFrame $webSocketFrame
     *
     * @return Server
     */
    public function setWebSocketFrame(WebSocketFrame $webSocketFrame): self
    {
        $this->webSocketFrame = $webSocketFrame;

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

        $event = new EventPayload();
        $event
            ->setNamespace($this->namespace)
            ->setName($eventName)
            ->setCallback($callback)
            ->setListeners([])
            ->setSocket($this);

        array_push($this->eventPool, $event);

        return $this;
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

        $this->webSocketServer->push($this->webSocketFrame->fd, Packet::encode($packetPayload));
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
            $this->webSocketServer->push($this->webSocketFrame->fd, $data);
        }
    }

    public function start()
    {
        new EngineServer($this->port, $this->configPayload, $this->eventPool);
    }
}
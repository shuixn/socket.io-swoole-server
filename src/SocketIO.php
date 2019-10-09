<?php

declare(strict_types=1);

namespace SocketIO;

use SocketIO\Engine\Server\ConfigPayload;
use SocketIO\Engine\WebSocket\Server as EngineWebSocketServer;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\WebSocket\Frame as WebSocketFrame;
use SocketIO\ExceptionHandler\InvalidEventException;

/**
 * Class SocketIO
 *
 * @package SocketIO
 */
class SocketIO
{
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
     * @return SocketIO
     */
    public function setWebSocketServer(WebSocketServer $webSocketServer): SocketIO
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
     * @return SocketIO
     */
    public function setWebSocketFrame(WebSocketFrame $webSocketFrame): SocketIO
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
     * @return SocketIO
     */
    public function setMessage(string $message): SocketIO
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string $eventName
     * @param callable $callback
     *
     * @return $this
     *
     * @throws InvalidEventException
     */
    public function on(string $eventName, callable $callback) : self
    {
        if (empty($eventName) || !is_callable($callback)) {
            throw new InvalidEventException('invalid Event');
        }

        $event = new Event();
        $event
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
        $response = [
            $eventName => $data
        ];

        $this->webSocketServer->push($this->webSocketFrame->fd, json_encode($response));
    }

    public function start()
    {
        new EngineWebSocketServer($this->port, $this->configPayload, $this->eventPool);
    }
}
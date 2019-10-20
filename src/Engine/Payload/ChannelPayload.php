<?php

declare(strict_types=1);

namespace SocketIO\Engine\Payload;

use Swoole\WebSocket\Server as WebSocketServer;

/**
 * Class ChannelPayload
 *
 * @package SocketIO\Engine\Payload
 */
class ChannelPayload
{
    /** @var WebSocketServer */
    private $webSocketServer;

    /** @var int */
    private $fd;

    /** @var string */
    private $message;

    /**
     * @return WebSocketServer
     */
    public function getWebSocketServer(): WebSocketServer
    {
        return $this->webSocketServer;
    }

    /**
     * @param WebSocketServer $webSocketServer
     * @return ChannelPayload
     */
    public function setWebSocketServer(WebSocketServer $webSocketServer): ChannelPayload
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
     * @return ChannelPayload
     */
    public function setFd(int $fd): ChannelPayload
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
     * @return ChannelPayload
     */
    public function setMessage(string $message): ChannelPayload
    {
        $this->message = $message;
        return $this;
    }
}
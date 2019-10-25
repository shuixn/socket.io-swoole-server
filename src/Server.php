<?php

declare(strict_types=1);

namespace SocketIO;

use Swoole\Coroutine\Channel;
use SocketIO\Engine\Payload\ChannelPayload;
use SocketIO\Engine\Payload\ConfigPayload;
use SocketIO\Engine\Server as EngineServer;
use SocketIO\Enum\Message\PacketTypeEnum;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Event\EventPayload;
use SocketIO\Event\EventPool;
use SocketIO\Storage\Table\ListenerSessionTable;
use SocketIO\Storage\Table\ListenerTable;
use SocketIO\Parser\WebSocket\Packet;
use SocketIO\Parser\WebSocket\PacketPayload;
use SocketIO\Storage\Table\NamespaceSessionTable;
use SocketIO\Storage\Table\SessionListenerTable;
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
                /** @var ChannelPayload $channelPayload */
                while($channelPayload = $chan->pop()) {
                    $webSocketServer = $channelPayload->getWebSocketServer() ?? null;
                    $message = $channelPayload->getMessage() ?? '';
                    $fd = $channelPayload->getFd() ?? 0;

                    $this->setWebSocketServer($webSocketServer);
                    $this->setMessage($message);
                    if ($fd != 0) {
                        $this->setFd($fd);
                    }

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
     * @param string $eventName
     * @param string $data
     *
     * @throws \Exception
     */
    public function broadcast(string $eventName, string $data)
    {
        $packetPayload = new PacketPayload();
        $packetPayload
            ->setNamespace($this->namespace)
            ->setEvent($eventName)
            ->setType(TypeEnum::MESSAGE)
            ->setPacketType(PacketTypeEnum::EVENT)
            ->setMessage(json_encode($data));

        $sids = NamespaceSessionTable::getInstance()->get($this->namespace);

        if (!empty($sids)) {
            $sidMapFd = SessionListenerTable::getInstance()->transformSessionToListener($sids);
            if (!empty($sidMapFd)) {
                foreach ($sidMapFd as $sid => $fd) {
                    if (isset($sidMapFd[$sid])) {
                        $this->webSocketServer->push($fd, Packet::encode($packetPayload));
                    } else {
                        // remove sid from NamespaceSessionTable
                        NamespaceSessionTable::getInstance()->pop($this->namespace, $sid);
                    }
                }
            } else {
                echo "broadcast failed, transform Sid to fd return empty\n";
            }
        } else {
            echo "broadcast failed, this namespace has not sid\n";
        }
    }

    public function start()
    {
        $this->initTables();

        new EngineServer($this->port, $this->configPayload, $this->callback, $this);
    }

    private function initTables()
    {
        NamespaceSessionTable::getInstance();
        ListenerSessionTable::getInstance();
        SessionListenerTable::getInstance();
        ListenerTable::getInstance();
    }
}
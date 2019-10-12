<?php

declare(strict_types=1);

namespace SocketIO\Engine;

use SocketIO\Engine\Payload\ConfigPayload;
use SocketIO\Engine\Payload\HttpResponsePayload;
use SocketIO\Engine\Payload\PollingPayload;
use SocketIO\Engine\Transport\Polling;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Event\EventListenerTable;
use SocketIO\Event\ListenerEventTable;
use SocketIO\Event\ListenerTable;
use SocketIO\Parser\Packet;
use SocketIO\Parser\PacketPayload;
use SocketIO\Server as SocketIOServer;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\WebSocket\Frame as WebSocketFrame;
use Swoole\Http\Request as HttpRequest;
use Swoole\Http\Response as HttpResponse;
use SocketIO\Event\EventPayload;

/**
 * Class Server
 *
 * @package SocketIO\Engine
 */
class Server
{
    /** @var WebSocketServer */
    protected $server;

    /** @var array */
    protected $serverEvents = [
        'request', 'open', 'message', 'close'
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
            'open_http_protocol' => true,
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

    public function onRequest(HttpRequest $request, HttpResponse $response)
    {
        if ($request->server['request_uri'] === '/socket.io/') {
            $eio = $request->get['eio'] ?? 0;
            $t = $request->get['t'] ?? '';
            $transport = $request->get['transport'] ?? '';
            $sid = $request->get['sid'] ?? '';

            switch ($request->server['request_method']) {
                case 'GET':
                    $pollingPayload = new PollingPayload();
                    $pollingPayload
                        ->setEio($eio)
                        ->setT($t)
                        ->setTransport($transport);

                    $polling = new Polling();
                    $responsePayload = $polling->handleGet($pollingPayload);
                    break;
                case 'POST':
                    $pollingPayload = new PollingPayload();
                    $pollingPayload
                        ->setEio($eio)
                        ->setT($t)
                        ->setTransport($transport)
                        ->setSid($sid);

                    $polling = new Polling();
                    $responsePayload = $polling->handlePost($pollingPayload);
                    break;
                default:
                    $responsePayload = new HttpResponsePayload();
                    $responsePayload->setStatus(400)->setHtml('method not found');
                    break;
            }
        } else {
            $responsePayload = new HttpResponsePayload();
            $responsePayload->setStatus(404)->setHtml('uri not found');
        }

        $response->status($responsePayload->getStatus());
        $response->end($responsePayload->getHtml());

        return;
    }

    /**
     * @param WebSocketServer $server
     * @param WebSocketFrame $frame
     *
     * @throws \Exception
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
                $server->push($frame->fd, TypeEnum::NOOP);
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

        /** @var EventPayload $event */
        foreach ($this->eventPool as $event) {
            $event->popListener($fd);
        }

        // todo clear table event and fd
    }

    /**
     * @param WebSocketServer $server
     * @param WebSocketFrame $frame
     * @param PacketPayload $packetPayload
     *
     * @throws \Exception
     */
    private function handleEvent(WebSocketServer $server, WebSocketFrame $frame, PacketPayload $packetPayload)
    {
        $namespace = $packetPayload->getNamespace();
        $eventName = $packetPayload->getEvent();

        $isExistEvent = false;

        /** @var EventPayload $event */
        foreach ($this->eventPool as $event) {
            if ($event->getNamespace() == $namespace && $event->getName() == $eventName) {
                $isExistEvent = true;

                $event->pushListener($frame->fd);
                EventListenerTable::getInstance()->push($namespace, $eventName, $frame->fd);
                ListenerEventTable::getInstance()->push($namespace, $eventName, strval($frame->fd));
                ListenerTable::getInstance()->push(strval($frame->fd));

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
<?php

declare(strict_types=1);

namespace SocketIO\Enum\Message;

use SocketIO\Enum\BaseEnum;

/**
 * Class TypeEnum
 *
 * EngineIO Packet
 *
 * @package SocketIO\Enum\Message
 */
class TypeEnum extends BaseEnum
{
    /**
     * Sent from the server when a new transport is opened (recheck)
     *
     * @message("open")
     */
    public const OPEN = 0;

    /**
     * Request the close of this transport but does not shutdown the connection itself.
     *
     * @message("close")
     */
    public const CLOSE = 1;

    /**
     * Sent by the client. Server should answer with a pong packet containing the same data.
     *
     * @message("ping")
     */
    public const PING = 2;

    /**
     * Sent by the server to respond to ping packets.
     *
     * @message("pong")
     */
    public const PONG = 3;

    /**
     * actual message, client and server should call their callbacks with the data.
     *
     * @message("message")
     */
    public const MESSAGE = 4;

    /**
     * Before engine.io switches a transport, it tests, if server and client can communicate over this transport.
     * If this  test succeed, the client sends an upgrade packets which requests the server to flush its cache on the old transport and switch to the new transport.
     *
     * @message("upgrade")
     */
    public const UPGRADE = 5;

    /**
     * A noop packet.
     * Used primarily to force a poll cycle when an incoming websocket connection is received.
     *
     * @message("noop")
     */
    public const NOOP = 6;
}
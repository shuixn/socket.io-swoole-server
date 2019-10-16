<?php

declare(strict_types=1);

namespace SocketIO\Enum\Message;

use SocketIO\Enum\BaseEnum;

/**
 * Class PacketTypeEnum
 *
 * SocketIO Packet
 *
 * @package SocketIO\Enum\Message
 */
class PacketTypeEnum extends BaseEnum
{
    /**
     * @message("connect")
     */
    public const CONNECT = 0;

    /**
     * @message("disconnect")
     */
    public const DISCONNECT = 1;

    /**
     * @message("event")
     */
    public const EVENT = 2;

    /**
     * @message("ack")
     */
    public const ACK = 3;

    /**
     * @message("error")
     */
    public const ERROR = 4;

    /**
     * @message("binary_event")
     */
    public const BINARY_EVENT = 5;

    /**
     * @message("binary_ack")
     */
    public const BINARY_ACK = 6;
}
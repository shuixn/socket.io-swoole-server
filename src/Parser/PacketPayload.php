<?php

declare(strict_types=1);

namespace SocketIO\Parser;

/**
 * Class PacketPayload
 *
 * @package SocketIO\Parser
 */
class PacketPayload
{
    /** @var int */
    private $type;

    /** @var int */
    private $packetType;

    /** @var string */
    private $event;

    /** @var string */
    private $message;

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return PacketPayload
     */
    public function setType(int $type): PacketPayload
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getPacketType(): int
    {
        return $this->packetType;
    }

    /**
     * @param int $packetType
     * @return PacketPayload
     */
    public function setPacketType(int $packetType): PacketPayload
    {
        $this->packetType = $packetType;
        return $this;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @return PacketPayload
     */
    public function setEvent(string $event): PacketPayload
    {
        $this->event = $event;
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
     * @return PacketPayload
     */
    public function setMessage(string $message): PacketPayload
    {
        $this->message = $message;
        return $this;
    }
}
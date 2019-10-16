<?php

declare(strict_types=1);

namespace SocketIO\Parser\Polling;

/**
 * Class Payload
 *
 * @package SocketIO\Parser\Polling
 */
class Payload
{
    /**
     * eg. 7:40/test
     * @var string
     */
    const PATTERN = '/([0-9]+):([0-9]+)\/([a-zA-Z0-9\s]+)/';

    /** @var int */
    private $length = 0;

    /** @var string */
    private $delimiter = ':';

    /** @var int */
    private $type = 0;

    /** @var int */
    private $packetType = 0;

    /** @var string */
    private $namespace = '/';

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return Payload
     */
    public function setLength(int $length): Payload
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     * @return Payload
     */
    public function setDelimiter(string $delimiter): Payload
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Payload
     */
    public function setType(int $type): Payload
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
     * @return Payload
     */
    public function setPacketType(int $packetType): Payload
    {
        $this->packetType = $packetType;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return Payload
     */
    public function setNamespace(string $namespace): Payload
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function decode(string $payload): Payload
    {
        if (preg_match(self::PATTERN, $payload, $matches)) {
            list(, $this->length, $types, $namespace) = $matches;

            $this->type = intval($types[0]);
            $this->packetType = intval($types[1]);
            $this->namespace = "/{$namespace}";
        }

        return $this;
    }
}
<?php

declare(strict_types=1);

namespace SocketIO\Event;

use Swoole\Coroutine\Channel;

/**
 * Class EventPayload
 *
 * @package SocketIO\Event
 */
class EventPayload
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $name;

    /** @var Channel */
    private $chan;

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return EventPayload
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return EventPayload
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getChan(): Channel
    {
        return $this->chan;
    }

    /**
     * @param Channel $chan
     * @return EventPayload
     */
    public function setChan(Channel $chan): EventPayload
    {
        $this->chan = $chan;
        return $this;
    }
}
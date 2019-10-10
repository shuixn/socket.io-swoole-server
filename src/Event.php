<?php

declare(strict_types=1);

namespace SocketIO;

/**
 * Class Event
 *
 * @package SocketIO
 */
class Event
{
    /** @var string */
    private $name;

    /** @var array */
    private $listeners;

    /** @var callable */
    private $callback;

    /** @var Server */
    private $socket;

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
     * @return Event
     */
    public function setName(string $name): Event
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * @param array $listeners
     *
     * @return Event
     */
    public function setListeners(array $listeners): Event
    {
        $this->listeners = $listeners;

        return $this;
    }

    /**
     * @param int $fd
     *
     * @return bool
     */
    public function pushListener(int $fd) : bool
    {
        if (!in_array($fd, $this->listeners)) {
            array_push($this->listeners, $fd);
        }

        return true;
    }

    /**
     * @param int $fd
     *
     * @return bool
     */
    public function popListener(int $fd) : bool
    {
        if (in_array($fd, $this->listeners)) {
            $this->listeners = array_diff($this->listeners, [ $fd ]);
        }

        return true;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     *
     * @return Event
     */
    public function setCallback(callable $callback): Event
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return Server
     */
    public function getSocket(): Server
    {
        return $this->socket;
    }

    /**
     * @param Server $socket
     * @return Event
     */
    public function setSocket(Server $socket): Event
    {
        $this->socket = $socket;
        return $this;
    }
}
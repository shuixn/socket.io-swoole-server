<?php

declare(strict_types=1);

namespace SocketIO\Event;

/**
 * Class EventPool
 *
 * @package SocketIO\Event
 */
class EventPool
{
    /** @var EventPool */
    private static $instance;

    /** @var array */
    private $pool = [];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param EventPayload $payload
     *
     * @return bool
     */
    public function push(EventPayload $payload) : bool
    {
        if (!empty($this->pool)) {
            /** @var EventPayload $item */
            foreach ($this->pool as $item) {
                if ($item->getNamespace() == $payload->getNamespace() && $item->getName() == $payload->getName()) {
                    return true;
                }
            }
        }

        array_push($this->pool, $payload);

        return true;
    }

    /**
     * @param string $namespace
     * @param string $eventName
     *
     * @return bool
     */
    public function isExist(string $namespace, string $eventName) : bool
    {
        /** @var EventPayload $item */
        foreach ($this->pool as $item) {
            if ($item->getNamespace() == $namespace && $item->getName() == $eventName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $namespace
     * @param string $eventName
     *
     * @return EventPayload|null
     */
    public function pop(string $namespace, string $eventName) : EventPayload
    {
        $data = null;
        $index = null;

        /** @var EventPayload $item */
        foreach ($this->pool as $key => $item) {
            if ($item->getNamespace() == $namespace && $item->getName() == $eventName) {
                $index = $key;
                $data = $item;
            }
        }

        if (!is_null($index)) {
            unset($this->pool[$index]);
        }

        return $data;
    }

    /**
     * @param string $namespace
     * @param string $eventName
     *
     * @return EventPayload|null
     */
    public function get(string $namespace, string $eventName) : EventPayload
    {
        /** @var EventPayload $item */
        foreach ($this->pool as $item) {
            if ($item->getNamespace() == $namespace && $item->getName() == $eventName) {
                return $item;
            }
        }

        return null;
    }
}
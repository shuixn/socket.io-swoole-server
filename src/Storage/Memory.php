<?php

declare(strict_types=1);

namespace SocketIO\Storage;

/**
 * Class Memory
 *
 * @package SocketIO\Storage
 */
class Memory implements MemoryInterface
{
    /** @var MemoryInterface */
    private $adapter;

    /**
     * Memory constructor.
     * @param MemoryInterface $adapter
     */
    public function __construct(MemoryInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function push(string $key, string $value): bool
    {
        return $this->adapter->push($key, $value);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function pop(string $key): string
    {
        return $this->adapter->pop($key);
    }
}
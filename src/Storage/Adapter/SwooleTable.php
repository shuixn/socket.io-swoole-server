<?php

declare(strict_types=1);

namespace SocketIO\Storage\Adapter;

use SocketIO\Storage\MemoryInterface;

/**
 * Class SwooleTable
 *
 * @package SocketIO\Storage
 */
class SwooleTable implements MemoryInterface
{
    public function __construct()
    {

    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function push(string $key, string $value): bool
    {

    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function pop(string $key): string
    {

    }
}
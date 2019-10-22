<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

/**
 * Class BaseTable
 *
 * @package SocketIO\Storage\Table
 */
class BaseTable
{
    /**
     * @var \Swoole\Table
     */
    protected $table;

    /** @var string */
    protected $tableKey;

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->table->count();
    }

    /**
     * @param string $key
     * @param array $value
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function setTable(string $key, array $value): bool
    {
        if ($this->table->set($key, $value)) {
            return true;
        } else {
            throw new \Exception('set table key error');
        }
    }
}
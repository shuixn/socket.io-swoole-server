<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class SessionTable
 *
 * @package SocketIO\Storage\Table
 */
class SessionTable
{
    /** @var string */
    const KEY = 'fd';

    /**
     * eg. sid => [ 'fd' => 1 ]
     * @var Table
     */
    private $table;

    /** @var ListenerTable */
    private static $instance = null;

    private function __construct(){}

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            self::$instance->initTable();
        }

        return self::$instance;
    }

    /**
     * @param int $raw default raw 1
     */
    private function initTable(int $raw = 65535)
    {
        $this->table = new Table($raw);
        $this->table->column(self::KEY, Table::TYPE_INT);
        $this->table->create();
    }

    /**
     * @param string $sid
     * @param int $fd
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function push(string $sid, int $fd) : bool
    {
        $value = [
            self::KEY => $fd
        ];

        return $this->setTable($sid, $value);
    }

    /**
     * @param string $sid
     * @return int
     * @throws \Exception
     */
    public function pop(string $sid) : int
    {
        if ($this->table->exist($sid)) {
            $value = $this->table->get($sid, self::KEY);
            if ($value) {
                if ($this->table->del($sid)) {
                    return $value;
                } else {
                    throw new \Exception('del table key return false');
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            return -1;
        }
    }

    /**
     * @param string $sid
     *
     * @return int
     *
     * @throws \Exception
     */
    public function get(string $sid) : int
    {
        $value = $this->table->get($sid, self::KEY);
        if ($value !== false) {
            return $value;
        } else {
            throw new \Exception('get table key return false');
        }
    }

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
    private function setTable(string $key, array $value): bool
    {
        if ($this->table->set($key, $value)) {
            return true;
        } else {
            throw new \Exception('set table key error');
        }
    }
}
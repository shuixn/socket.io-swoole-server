<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class ListenerSessionTable
 *
 * eg. fd => [ 'sid' => 'xyz' ]
 *
 * @package SocketIO\Storage\Table
 */
class ListenerSessionTable extends BaseTable
{
    /** @var ListenerSessionTable */
    private static $instance = null;

    private function __construct(){}

    /**
     * @param int $row
     * @param int $size
     *
     * @return ListenerSessionTable
     */
    public static function getInstance(int $row = 65535, int $size = 4 * 1024 * 1024)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            self::$instance->initTable($row, $size);
        }

        return self::$instance;
    }

    /**
     * @param int $row
     * @param int $size
     */
    private function initTable(int $row, int $size)
    {
        $this->tableKey = 'sid';

        $this->table = new Table($row);
        $this->table->column($this->tableKey, Table::TYPE_STRING, $size);
        $this->table->create();
    }

    /**
     * @param string $sid
     * @param string $fd
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function push(string $fd, string $sid) : bool
    {
        $value = [
            $this->tableKey => $sid
        ];

        return $this->setTable($fd, $value);
    }

    /**
     * @param string $fd
     * @return string
     * @throws \Exception
     */
    public function pop(string $fd) : string
    {
        if ($this->table->exist($fd)) {
            $value = $this->table->get($fd, $this->tableKey);
            if ($value) {
                if ($this->table->del($fd)) {
                    return $value;
                } else {
                    throw new \Exception('del table key return false');
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            return '';
        }
    }

    /**
     * @param string $key
     *
     * @return string
     *
     * @throws \Exception
     */
    public function get(string $key) : string
    {
        $value = $this->table->get($key, $this->tableKey);
        if ($value !== false) {
            return $value;
        } else {
            throw new \Exception('get table key return false');
        }
    }
}
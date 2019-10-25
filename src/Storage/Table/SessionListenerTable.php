<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class SessionListenerTable
 *
 * sid => [ 'fd' => 1 ]
 *
 * @package SocketIO\Storage\Table
 */
class SessionListenerTable extends BaseTable
{
    /** @var SessionListenerTable */
    private static $instance = null;

    private function __construct(){}

    /**
     * @param int $row
     *
     * @return SessionListenerTable
     */
    public static function getInstance(int $row = 65535)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            self::$instance->initTable($row);
        }

        return self::$instance;
    }

    /**
     * @param int $row
     */
    private function initTable(int $row)
    {
        $this->tableKey = 'fd';

        $this->table = new Table($row);
        $this->table->column($this->tableKey, Table::TYPE_INT);
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
            $this->tableKey => $fd
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
            $value = $this->table->get($sid, $this->tableKey);
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
        $value = $this->table->get($sid, $this->tableKey);
        if ($value !== false) {
            return $value;
        } else {
            return -1;
        }
    }

    /**
     * @param array $sids
     *
     * @return array
     *
     * @throws \Exception
     */
    public function transformSessionToListener(array $sids) : array
    {
        if (!empty($sids)) {
            $fds = [];
            foreach ($sids as $sid) {
                $fd = $this->get($sid);
                if ($fd !== -1) {
                    $fds[$sid] = $fd;
                }
            }

            return $fds;
        }

        return [];
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->table->count();
    }
}
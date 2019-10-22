<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class ListenerTable
 *
 * @package SocketIO\Storage\Table
 */
class ListenerTable extends BaseTable
{
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
     * @param int $row default $row 1
     * @param int $size default size 4M
     */
    private function initTable(int $row = 1, int $size = 4 * 1024 * 1024)
    {
        $this->tableKey = 'fds';

        $this->table = new Table($row);
        $this->table->column($this->tableKey, Table::TYPE_STRING, $size);
        $this->table->create();
    }

    /**
     * @param string $fd
     * @return bool
     * @throws \Exception
     */
    public function push(string $fd) : bool
    {
        if ($this->table->exist($this->tableKey)) {
            $value = $this->table->get($this->tableKey, $this->tableKey);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (in_array($fd, $value)) {
                    return true;
                } else {
                    array_push($value, $fd);
                    $value = [
                        $this->tableKey => json_encode($value)
                    ];

                    return $this->setTable($this->tableKey, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                $this->tableKey => json_encode([$fd])
            ];

            return $this->setTable($this->tableKey, $value);
        }
    }

    /**
     * @param string $fd
     * @return bool
     * @throws \Exception
     */
    public function pop(string $fd) : bool
    {
        if ($this->table->exist($this->tableKey)) {
            $value = $this->table->get($this->tableKey, $this->tableKey);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (!in_array($fd, $value)) {
                    return true;
                } else {
                    $value = array_diff($value, [$fd]);
                    $value = [
                        $this->tableKey => json_encode($value)
                    ];

                    return $this->setTable($this->tableKey, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            return true;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getListener() : array
    {
        $value = $this->table->get($this->tableKey, $this->tableKey);
        if ($value) {
            $value = json_decode($value, true);
            if (is_null($value)) {
                throw new \Exception('json decode failed: ' . json_last_error_msg());
            }

            return $value;
        } else {
            throw new \Exception('get table key return false');
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
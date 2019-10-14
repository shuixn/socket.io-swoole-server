<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class ListenerTable
 *
 * @package SocketIO\Storage\Table
 */
class ListenerTable
{
    /** @var string */
    const KEY = 'fds';

    /**
     * eg. fds => [ fds => "['1', '2']" ]
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
     * @param int $size default size 4M
     */
    private function initTable(int $raw = 1, int $size = 4 * 1024 * 1024)
    {
        $this->table = new Table($raw);
        $this->table->column(self::KEY, Table::TYPE_STRING, $size);
        $this->table->create();
    }

    /**
     * @param string $fd
     * @return bool
     * @throws \Exception
     */
    public function push(string $fd) : bool
    {
        if ($this->table->exist(self::KEY)) {
            $value = $this->table->get(self::KEY, self::KEY);
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
                        self::KEY => json_encode($value)
                    ];

                    return $this->setTable(self::KEY, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                self::KEY => json_encode([$fd])
            ];

            return $this->setTable(self::KEY, $value);
        }
    }

    /**
     * @param string $fd
     * @return bool
     * @throws \Exception
     */
    public function pop(string $fd) : bool
    {
        if ($this->table->exist(self::KEY)) {
            $value = $this->table->get(self::KEY, self::KEY);
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
                        self::KEY => json_encode($value)
                    ];

                    return $this->setTable(self::KEY, $value);
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
        $value = $this->table->get(self::KEY, self::KEY);
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
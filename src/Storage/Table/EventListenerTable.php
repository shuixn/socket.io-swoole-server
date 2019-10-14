<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class EventListenerTable
 *
 * @package SocketIO\Storage\Table
 */
class EventListenerTable
{
    /** @var string */
    const KEY = 'fds';

    /**
     * eg. Namespace#Event => ['fds' => '[1,2,3]']
     * @var Table
     */
    private $table;

    /** @var EventListenerTable */
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
     * @param int $raw default raw 1000
     * @param int $size default size 4M
     */
    private function initTable(int $raw = 1000, int $size = 4 * 1024 * 1024)
    {
        $this->table = new Table($raw);
        $this->table->column(self::KEY, Table::TYPE_STRING, $size);
        $this->table->create();
    }

    /**
     * @param string $namespace
     * @param string $event
     * @param int $fd
     * @return bool
     * @throws \Exception
     */
    public function push(string $namespace, string $event, int $fd) : bool
    {
        $uniqueKey = $this->getUniqueKey($namespace, $event);
        if ($this->table->exist($uniqueKey)) {
            $value = $this->table->get($uniqueKey, self::KEY);
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

                    return $this->setTable($uniqueKey, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                self::KEY => json_encode([$fd])
            ];

            return $this->setTable($uniqueKey, $value);
        }
    }

    /**
     * @param string $namespace
     * @param string $event
     * @param int $fd
     * @return bool
     * @throws \Exception
     */
    public function pop(string $namespace, string $event, int $fd) : bool
    {
        $uniqueKey = $this->getUniqueKey($namespace, $event);
        if ($this->table->exist($uniqueKey)) {
            $value = $this->table->get($uniqueKey, self::KEY);
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

                    return $this->setTable($uniqueKey, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            return true;
        }
    }

    /**
     * @param string $namespace
     * @param string $event
     * @return bool
     */
    public function destroy(string $namespace, string $event) : bool
    {
        $uniqueKey = $this->getUniqueKey($namespace, $event);

        return $this->table->del($uniqueKey);
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->table->count();
    }

    /**
     * @param string $uniqueKey
     * @param array $value
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function setTable(string $uniqueKey, array $value): bool
    {
        if ($this->table->set($uniqueKey, $value)) {
            return true;
        } else {
            throw new \Exception('set table key error');
        }
    }

    /**
     * @param string $namespace
     * @param string $event
     *
     * @return string
     */
    private function getUniqueKey(string $namespace, string $event) : string
    {
        return "{$namespace}#{$event}";
    }
}
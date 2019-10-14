<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class ListenerEventTable
 *
 * @package SocketIO\Storage\Table
 */
class ListenerEventTable
{
    /** @var string */
    const KEY = 'events';

    /**
     * eg. fd => [ events => "['Namespace#Event#1', 'Namespace#Event#2']" ]
     * @var Table
     */
    private $table;

    /** @var ListenerEventTable */
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
     * @param int $raw default raw 65536
     * @param int $size default size 4M
     */
    private function initTable(int $raw = 65536, int $size = 4 * 1024 * 1024)
    {
        $this->table = new Table($raw);
        $this->table->column(self::KEY, Table::TYPE_STRING, $size);
        $this->table->create();
    }

    /**
     * @param string $namespace
     * @param string $event
     * @param string $fd
     * @return bool
     * @throws \Exception
     */
    public function push(string $namespace, string $event, string $fd) : bool
    {
        $event = $this->getEvent($namespace, $event);
        if ($this->table->exist($fd)) {
            $value = $this->table->get($fd, self::KEY);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (in_array($event, $value)) {
                    return true;
                } else {
                    array_push($value, $event);
                    $value = [
                        self::KEY => json_encode($value)
                    ];

                    return $this->setTable($fd, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                self::KEY => json_encode([$event])
            ];

            return $this->setTable($fd, $value);
        }
    }

    /**
     * @param string $namespace
     * @param string $event
     * @param string $fd
     * @return bool
     * @throws \Exception
     */
    public function pop(string $namespace, string $event, string $fd) : bool
    {
        $event = $this->getEvent($namespace, $event);
        if ($this->table->exist($fd)) {
            $value = $this->table->get($fd, self::KEY);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (!in_array($event, $value)) {
                    return true;
                } else {
                    $value = array_diff($value, [$event]);
                    $value = [
                        self::KEY => json_encode($value)
                    ];

                    return $this->setTable($fd, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            return true;
        }
    }

    /**
     * @param int $fd
     * @return bool
     */
    public function destroy(int $fd) : bool
    {
        return $this->table->del($fd);
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->table->count();
    }

    /**
     * @param string $fd
     * @param array $value
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function setTable(string $fd, array $value): bool
    {
        if ($this->table->set($fd, $value)) {
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
    private function getEvent(string $namespace, string $event) : string
    {
        return "{$namespace}#{$event}";
    }
}
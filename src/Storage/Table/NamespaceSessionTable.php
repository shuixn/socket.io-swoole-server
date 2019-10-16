<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class NamespaceSessionTable
 *
 * @package SocketIO\Storage\Table
 */
class NamespaceSessionTable
{
    /** @var string */
    const KEY = 'sids';

    /**
     * eg. Namespace => ['sids' => '[1,2,3]']
     * @var Table
     */
    private $table;

    /** @var NamespaceSessionTable */
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
     * @param string $sid
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function push(string $namespace, string $sid) : bool
    {
        if ($this->table->exist($namespace)) {
            $value = $this->table->get($namespace, self::KEY);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (in_array($sid, $value)) {
                    return true;
                } else {
                    array_push($value, $sid);
                    $value = [
                        self::KEY => json_encode($value)
                    ];

                    return $this->setTable($namespace, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                self::KEY => json_encode([$sid])
            ];

            return $this->setTable($namespace, $value);
        }
    }

    /**
     * @param string $namespace
     * @param string $sid
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function pop(string $namespace, string $sid) : bool
    {
        if ($this->table->exist($namespace)) {
            $value = $this->table->get($namespace, self::KEY);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (!in_array($sid, $value)) {
                    return true;
                } else {
                    $value = array_diff($value, [$sid]);
                    $value = [
                        self::KEY => json_encode($value)
                    ];

                    return $this->setTable($namespace, $value);
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
     * @return bool
     */
    public function destroy(string $namespace) : bool
    {
        return $this->table->del($namespace);
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
}
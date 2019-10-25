<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class NamespaceSessionTable
 *
 * Namespace => ['sids' => '[1,2,3]']
 *
 * @package SocketIO\Storage\Table
 */
class NamespaceSessionTable extends BaseTable
{
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
     * @param int $row default $row 1000
     * @param int $size default size 4M
     */
    private function initTable(int $row = 1000, int $size = 4 * 1024 * 1024)
    {
        $this->tableKey = 'sids';

        $this->table = new Table($row);
        $this->table->column($this->tableKey, Table::TYPE_STRING, $size);
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
            $value = $this->table->get($namespace, $this->tableKey);
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
                        $this->tableKey => json_encode($value)
                    ];

                    return $this->setTable($namespace, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                $this->tableKey => json_encode([$sid])
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
            $value = $this->table->get($namespace, $this->tableKey);
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
                        $this->tableKey => json_encode($value)
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
     * @param string $namespace
     *
     * @return array
     *
     * @throws \Exception
     */
    public function get(string $namespace) : array
    {
        $value = $this->table->get($namespace, $this->tableKey);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            throw new \Exception('get table key return false');
        }
    }
}
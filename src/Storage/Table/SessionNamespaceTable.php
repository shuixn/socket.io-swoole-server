<?php

declare(strict_types=1);

namespace SocketIO\Storage\Table;

use Swoole\Table;

/**
 * Class SessionNamespaceTable
 *
 * sid => ['namespaces' => '["/"]']
 *
 * @package SocketIO\Storage\Table
 */
class SessionNamespaceTable extends BaseTable
{
    /** @var SessionNamespaceTable */
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
        $this->tableKey = 'namespaces';

        $this->table = new Table($row);
        $this->table->column($this->tableKey, Table::TYPE_STRING, $size);
        $this->table->create();
    }

    /**
     * @param string $sid
     * @param string $namespace
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function push(string $sid, string $namespace) : bool
    {
        if ($this->table->exist($sid)) {
            $value = $this->table->get($sid, $this->tableKey);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (in_array($namespace, $value)) {
                    return true;
                } else {
                    array_push($value, $namespace);
                    $value = [
                        $this->tableKey => json_encode($value)
                    ];

                    return $this->setTable($sid, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            $value = [
                $this->tableKey => json_encode([$namespace])
            ];

            return $this->setTable($sid, $value);
        }
    }

    /**
     * @param string $sid
     * @param string $namespace
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function pop(string $sid, string $namespace) : bool
    {
        if ($this->table->exist($sid)) {
            $value = $this->table->get($sid, $this->tableKey);
            if ($value) {
                $value = json_decode($value, true);
                if (is_null($value)) {
                    throw new \Exception('json decode failed: ' . json_last_error_msg());
                }
                if (!in_array($namespace, $value)) {
                    return true;
                } else {
                    $value = array_diff($value, [$namespace]);
                    $value = [
                        $this->tableKey => json_encode($value)
                    ];

                    return $this->setTable($sid, $value);
                }
            } else {
                throw new \Exception('get table key return false');
            }
        } else {
            return true;
        }
    }

    /**
     * @param string $sid
     * @return bool
     */
    public function destroy(string $sid) : bool
    {
        return $this->table->del($sid);
    }

    /**
     * @param string $key
     *
     * @return array
     *
     * @throws \Exception
     */
    public function get(string $key) : array
    {
        $value = $this->table->get($key, $this->tableKey);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            throw new \Exception('get table key return false');
        }
    }
}
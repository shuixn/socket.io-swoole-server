<?php

declare(strict_types=1);

namespace SocketIO\Engine\Payload;

/**
 * Class ConfigPayload
 *
 * @package SocketIO\Engine\Payload
 */
class ConfigPayload
{
    /**
     * default start 2 worker
     * @var int
     */
    private $workerNum = 2;

    /**
     * default 0 means not daemon
     * @var int
     */
    private $daemonize = 0;

    /**
     * @return int
     */
    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }

    /**
     * @param int $workerNum
     * @return ConfigPayload
     */
    public function setWorkerNum(int $workerNum = 2): ConfigPayload
    {
        $this->workerNum = $workerNum >= 2 ? $workerNum : 2;
        return $this;
    }

    /**
     * @return int
     */
    public function getDaemonize(): int
    {
        return $this->daemonize;
    }

    /**
     * @param int $daemonize
     * @return ConfigPayload
     */
    public function setDaemonize(int $daemonize): ConfigPayload
    {
        $this->daemonize = $daemonize;
        return $this;
    }
}
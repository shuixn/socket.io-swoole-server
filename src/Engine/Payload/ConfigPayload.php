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
    /** @var int */
    private $workerNum;

    /** @var int */
    private $daemonize;

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
    public function setWorkerNum(int $workerNum): ConfigPayload
    {
        $this->workerNum = $workerNum;
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
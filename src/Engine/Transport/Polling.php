<?php

declare(strict_types=1);

namespace SocketIO\Engine\Transport;

/**
 * Class Polling
 *
 * @package SocketIO\Engine\Transport
 */
class Polling
{
    /** @var string */
    protected $sid;

    /** @var bool */
    protected $isBinary = true;

    /** @var int */
    protected $pingInterval = 25000;

    /** @var int */
    protected $pingTimeout = 60000;

    /** @var array */
    protected $upgrades = ["websocket"];

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getSid() : string
    {
        if (empty($this->sid)) {
            $this->sid = bin2hex(pack('d', microtime(true)).pack('N', function_exists('random_int') ? random_int(1, 100000000): rand(1, 100000000)));
        }

        return $this->sid;
    }
}
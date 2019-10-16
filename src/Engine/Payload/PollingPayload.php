<?php

declare(strict_types=1);

namespace SocketIO\Engine\Payload;

/**
 * Class PollingPayload
 *
 * @package SocketIO\Engine\Payload
 */
class PollingPayload
{
    /** @var array */
    private $headers = [];

    /** @var string */
    private $requestPayload = '';

    /**
     * engine io version
     * @var int
     */
    private $eio = 3;

    /**
     * polling or websocket
     * @var string
     */
    private $transport = '';

    /**
     * random string
     * @var string
     */
    private $t = '';

    /**
     * client session id
     * @var string
     */
    private $sid = '';

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return PollingPayload
     */
    public function setHeaders(array $headers): PollingPayload
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestPayload(): string
    {
        return $this->requestPayload;
    }

    /**
     * @param string $requestPayload
     * @return PollingPayload
     */
    public function setRequestPayload(string $requestPayload): PollingPayload
    {
        $this->requestPayload = $requestPayload;
        return $this;
    }

    /**
     * @return int
     */
    public function getEio(): int
    {
        return $this->eio;
    }

    /**
     * @param int $eio
     * @return PollingPayload
     */
    public function setEio(int $eio): PollingPayload
    {
        $this->eio = $eio;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransport(): string
    {
        return $this->transport;
    }

    /**
     * @param string $transport
     * @return PollingPayload
     */
    public function setTransport(string $transport): PollingPayload
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * @return string
     */
    public function getT(): string
    {
        return $this->t;
    }

    /**
     * @param string $t
     * @return PollingPayload
     */
    public function setT(string $t): PollingPayload
    {
        $this->t = $t;
        return $this;
    }

    /**
     * @return string
     */
    public function getSid(): string
    {
        return $this->sid;
    }

    /**
     * @param string $sid
     * @return PollingPayload
     */
    public function setSid(string $sid): PollingPayload
    {
        $this->sid = $sid;
        return $this;
    }
}
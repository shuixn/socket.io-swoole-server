<?php

declare(strict_types=1);

namespace SocketIO\Engine\Payload;

/**
 * Class HttpResponsePayload
 *
 * @package SocketIO\Engine\Payload
 */
class HttpResponsePayload
{
    /** @var array */
    private $header;

    /** @var array */
    private $cookie;

    /** @var int */
    private $status;

    /** @var string */
    private $html;

    /** @var array */
    private $chunkData;

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param array $header
     * @return HttpResponsePayload
     */
    public function setHeader(array $header): HttpResponsePayload
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return array
     */
    public function getCookie(): array
    {
        return $this->cookie;
    }

    /**
     * @param array $cookie
     * @return HttpResponsePayload
     */
    public function setCookie(array $cookie): HttpResponsePayload
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return HttpResponsePayload
     */
    public function setStatus(int $status): HttpResponsePayload
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return HttpResponsePayload
     */
    public function setHtml(string $html): HttpResponsePayload
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return array
     */
    public function getChunkData(): array
    {
        return $this->chunkData;
    }

    /**
     * @param array $chunkData
     * @return HttpResponsePayload
     */
    public function setChunkData(array $chunkData): HttpResponsePayload
    {
        $this->chunkData = $chunkData;
        return $this;
    }
}
<?php

declare(strict_types=1);

namespace SocketIO\Engine\Transport;

use SocketIO\Engine\Payload\HttpResponsePayload;
use SocketIO\Engine\Payload\PollingPayload;

/**
 * Class Polling
 *
 * @package SocketIO\Engine\Transport
 */
class Polling
{
    /**
     * @param PollingPayload $pollingPayload
     *
     * @return HttpResponsePayload
     */
    public function handleGet(PollingPayload $pollingPayload) : HttpResponsePayload
    {

    }

    /**
     * @param PollingPayload $pollingPayload
     *
     * @return HttpResponsePayload
     */
    public function handlePost(PollingPayload $pollingPayload) : HttpResponsePayload
    {

    }
}
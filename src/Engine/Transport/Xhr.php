<?php

declare(strict_types=1);

namespace SocketIO\Engine\Transport;

use SocketIO\Engine\Payload\HttpResponsePayload;
use SocketIO\Engine\Payload\PollingPayload;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Parser\Polling\Packet;

/**
 * Class Xhr
 *
 * @package SocketIO\Engine\Transport
 */
class Xhr extends Polling
{
    /**
     * @param PollingPayload $pollingPayload
     *
     * @return HttpResponsePayload
     *
     * @throws \Exception
     */
    public function handleGet(PollingPayload $pollingPayload) : HttpResponsePayload
    {
        $responsePayload = new HttpResponsePayload();

        if (empty($pollingPayload->getSid())) {
            $data = [
                'sid' => $this->getSid(),
                'pingInterval' => $this->pingInterval,
                'pingTimeout' => $this->pingTimeout,
                'upgrades' => $this->upgrades
            ];

            $engineIoPacket = Packet::encode(TypeEnum::OPEN, json_encode($data));
            $socketIoPacket = Packet::encode(TypeEnum::MESSAGE, '0');

            $responsePayload->setChunkData($engineIoPacket . $socketIoPacket);
        } else {
            $data = 0;
        }

        $responsePayload->setHeader([
            "Content-Type" => "application/octet-stream",
            "Access-Control-Allow-Credentials" => 'true',
            "Access-Control-Allow-Origin" => $pollingPayload->getHeaders()['origin'],
            'Content-Length'=> strlen($responsePayload->getChunkData()),
            'X-XSS-Protection' => '0',
        ]);

        $responsePayload->setStatus(200);

        return $responsePayload;
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
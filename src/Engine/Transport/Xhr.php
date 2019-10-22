<?php

declare(strict_types=1);

namespace SocketIO\Engine\Transport;

use SocketIO\Engine\Payload\HttpResponsePayload;
use SocketIO\Engine\Payload\PollingPayload;
use SocketIO\Enum\Message\PacketTypeEnum;
use SocketIO\Enum\Message\TypeEnum;
use SocketIO\Parser\Polling\Packet;
use SocketIO\Parser\Polling\Payload;
use SocketIO\Storage\Table\NamespaceSessionTable;
use SocketIO\Storage\Table\SessionListenerTable;
use SocketIO\Storage\Table\SessionNamespaceTable;

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

            $socketIoPacket = Packet::encode(PacketTypeEnum::CONNECT, json_encode($data));
            $engineIoPacket = Packet::encode(TypeEnum::MESSAGE, '0');

            $responsePayload->setChunkData($socketIoPacket . $engineIoPacket);

            SessionListenerTable::getInstance()->push($this->getSid(), -1);

            NamespaceSessionTable::getInstance()->push('/', $this->getSid());

            SessionNamespaceTable::getInstance()->push($this->getSid(), '/');
        } else {
            // hanging request before websocket connected
            $isHanging = true;
            while ($isHanging) {
                $sessionId = SessionListenerTable::getInstance()->get($pollingPayload->getSid());
                if ($sessionId !== -1) {
                    $isHanging = false;
                }
            }

            $engineIoPacket = Packet::encode(TypeEnum::NOOP, '');
            $responsePayload->setChunkData($engineIoPacket);
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
     *
     * @throws \Exception
     */
    public function handlePost(PollingPayload $pollingPayload) : HttpResponsePayload
    {
        $responsePayload = new HttpResponsePayload();

        $payloadData = new Payload();
        $payloadData->decode($pollingPayload->getRequestPayload());

        if ($this->enterNamespace($pollingPayload->getSid(), $payloadData)) {
            $responsePayload->setHtml('ok');

            $responsePayload->setHeader([
                "Content-Type" => "Content-type: text/html;charset=UTF-8",
                "Access-Control-Allow-Credentials" => 'true',
                "Access-Control-Allow-Origin" => $pollingPayload->getHeaders()['origin'],
                'Content-Length'=> strlen($responsePayload->getHtml()),
                'X-XSS-Protection' => '0',
            ]);

            $responsePayload->setStatus(200);
        } else {
            $responsePayload->setHtml('enter namespace failed');

            $responsePayload->setHeader([
                "Content-Type" => "Content-type: text/html;charset=UTF-8",
                "Access-Control-Allow-Credentials" => 'true',
                "Access-Control-Allow-Origin" => $pollingPayload->getHeaders()['origin'],
                'Content-Length'=> strlen($responsePayload->getHtml()),
                'X-XSS-Protection' => '0',
            ]);

            $responsePayload->setStatus(500);
        }

        return $responsePayload;
    }

    /**
     * @param string $sid
     * @param Payload $payloadData
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function enterNamespace(string $sid, Payload $payloadData): bool
    {
        if (!empty($sid)
            && $payloadData->getType() === TypeEnum::MESSAGE
            && $payloadData->getPacketType() === PacketTypeEnum::CONNECT
            && !empty($payloadData->getNamespace())) {

            return NamespaceSessionTable::getInstance()->push($payloadData->getNamespace(), $sid);
        }

        return false;
    }
}
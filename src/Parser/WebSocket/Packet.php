<?php

declare(strict_types=1);

namespace SocketIO\Parser\WebSocket;

use SocketIO\Enum\Message\PacketTypeEnum;
use SocketIO\Enum\Message\TypeEnum;

/**
 * Class Packet
 *
 * @package SocketIO\Parser\WebSocket
 */
class Packet
{
    /** @var string */
    const MSG_PATTERN = '/([0-9]+)[\/]*([a-zA-Z0-9]*)[,]*([\s\S]*)/';

    /**
     * param 1: Type
     * param 2: PacketType
     * param 3: Namespace
     * param 4: Message
     * @var string
     */
    CONST MSG_TEMPLATE = '%s%s%s,%s';

    /**
     * @param string $rawData
     *
     * @return PacketPayload
     */
    public static function decode(string $rawData) : PacketPayload
    {
        $packetPayload = new PacketPayload();
        $packetPayload->setRawData($rawData);

        if (preg_match_all(self::MSG_PATTERN, $rawData, $matches)) {
            list(, $code, $namespace, $packet) = $matches;
            $code = current($code);
            $namespace = current($namespace);
            $packet = current($packet);

            $packetData = json_decode($packet, true);
            if (!empty($packetData)) {
                $event = $packetData[0];
                $message = json_encode($packetData[1]);
            } else {
                $event = '';
                $message = '';

                if ($namespace == 'probe') {
                    $namespace = '';
                    $message = 'probe';
                }
            }

            $packetPayload
                ->setNamespace($namespace)
                ->setEvent($event)
                ->setMessage($message);
        } else {
            // wrong packet
            return $packetPayload;
        }

        switch (strlen($code)) {
            case 0:
                break;
            case 1:
                switch ($code) {
                    case TypeEnum::PING:
                        $packetPayload->setType(TypeEnum::PING);
                        break;
                    case TypeEnum::UPGRADE:
                        $packetPayload->setType(TypeEnum::UPGRADE);
                        break;
                }
                break;

            case 2:
                switch ($code) {
                    case TypeEnum::MESSAGE . PacketTypeEnum::DISCONNECT:
                        $packetPayload
                            ->setType(TypeEnum::MESSAGE)
                            ->setPacketType(PacketTypeEnum::DISCONNECT);

                        break;
                    case TypeEnum::MESSAGE . PacketTypeEnum::EVENT:
                        $packetPayload
                            ->setType(TypeEnum::MESSAGE)
                            ->setPacketType(PacketTypeEnum::EVENT);
                        break;
                }
                break;
            default:
                switch ($code[0]) {
                    case TypeEnum::MESSAGE:
                        switch ($code[1]) {
                            case PacketTypeEnum::EVENT:
                                $packetPayload
                                    ->setType(TypeEnum::MESSAGE)
                                    ->setPacketType(PacketTypeEnum::EVENT);
                                break;

                            case PacketTypeEnum::ACK:
                                $packetPayload
                                    ->setType(TypeEnum::MESSAGE)
                                    ->setPacketType(PacketTypeEnum::ACK);
                                break;
                        }
                        break;
                }
                break;
        }

        return $packetPayload;
    }

    /**
     * @param PacketPayload $packetPayload
     *
     * @return string
     */
    public static function encode(PacketPayload $packetPayload) : string
    {
        $message = json_encode([
            $packetPayload->getEvent(),
            json_decode($packetPayload->getMessage())
        ]);

        return sprintf(self::MSG_TEMPLATE,
            $packetPayload->getType(),
            $packetPayload->getPacketType(),
            $packetPayload->getNamespace(),
            $message
        );
    }
}
<?php

declare(strict_types=1);

namespace SocketIO\Parser;

use SocketIO\Enum\Message\PacketType;
use SocketIO\Enum\Message\TypeEnum;

/**
 * Class Packet
 *
 * @package SocketIO\Parser
 */
class Packet
{
    /** @var string */
    const PATTERN = '/([0-9]+)[\/]*([a-zA-Z0-9]*)[,]*([\s\S]*)/';

    /**
     * @param string $rawData
     *
     * @return PacketPayload
     */
    public static function decode(string $rawData) : PacketPayload
    {
        $packetPayload = new PacketPayload();
        $packetPayload->setRawData($rawData);

        if (preg_match_all(self::PATTERN, $rawData, $matches)) {
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
                }
                break;
            case 2:
                switch ($code) {
                    case TypeEnum::MESSAGE . PacketType::DISCONNECT:
                        $packetPayload
                            ->setType(TypeEnum::MESSAGE)
                            ->setPacketType(PacketType::DISCONNECT);

                        break;
                    case TypeEnum::MESSAGE . PacketType::EVENT:
                        $packetPayload
                            ->setType(TypeEnum::MESSAGE)
                            ->setPacketType(PacketType::EVENT);
                        break;
                }
                break;
            default:
                switch ($code[0]) {
                    case TypeEnum::MESSAGE:
                        switch ($code[1]) {
                            case PacketType::EVENT:
                                $packetPayload
                                    ->setType(TypeEnum::MESSAGE)
                                    ->setPacketType(PacketType::EVENT);
                                break;

                            case PacketType::ACK:
                                $packetPayload
                                    ->setType(TypeEnum::MESSAGE)
                                    ->setPacketType(PacketType::ACK);
                                break;
                        }
                        break;
                }
                break;
        }

        return $packetPayload;
    }

    public static function encode(array $data)
    {

    }
}
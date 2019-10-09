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
    /**
     * @param string $data
     *
     * @return PacketPayload
     */
    public static function decode(string $data) : PacketPayload
    {
        $packetPayload = new PacketPayload();

        if ($index = strpos($data, '[')) {
            $code = substr($data, 0, $index);

            // todo json error catch
            $packet = substr($data, $index);
            if (!empty($packet)) {
                $data = json_decode($packet, true);
                if (!empty($data)) {
                    $event = $data[0];
                    $message = strval($data[1]);
                } else {
                    $event = '';
                    $message = '';
                }
            } else {
                $event = '';
                $message = '';
            }
        } else {
            $code = $data;
            $event = '';
            $message = '';
        }

        $packetPayload->setEvent($event)->setMessage($message);

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
                    case TypeEnum::MESSAGE . PacketType::DISCONNECT:   //client disconnect
                        $packetPayload
                            ->setType(TypeEnum::MESSAGE)
                            ->setPacketType(PacketType::DISCONNECT);

                        break;
                    case TypeEnum::MESSAGE . PacketType::EVENT:   //client message
                        $packetPayload
                            ->setType(TypeEnum::MESSAGE)
                            ->setPacketType(PacketType::EVENT);
                        break;
                }
                break;
            default:
                switch ($code[0]) {
                    case TypeEnum::MESSAGE:   //client message
                        switch ($code[1]) {
                            case PacketType::EVENT:   //client message with ack
                                $packetPayload
                                    ->setType(TypeEnum::MESSAGE)
                                    ->setPacketType(PacketType::EVENT);
                                break;

                            case PacketType::ACK:   //client reply to message with ack
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
<?php

declare(strict_types=1);

namespace SocketIO\Parser\Polling;

/**
 * Class Packet
 *
 * @package SocketIO\Parser\Polling
 */
class Packet
{
    /**
     * @param int $type
     * @param string $data
     *
     * @return string <0 for string data, 1 for binary data><Any number of numbers between 0 and 9><The number 255><packet1 (first type, then data)>[...]
     * eg. \x00\x01\x00\x09\xff . packet
     */
    public static function encode(int $type, string $data) : string
    {
        $packet = strval($type) . $data;

        $packetLength = strval(strlen($packet));
        $packetLengthBits = strlen($packetLength);

        $sizeCode = chr(0);
        for ($i = 0; $i < $packetLengthBits; $i++) {
            $sizeCode .= chr(intval($packetLength[$i]));
        }

        $sizeCode .= chr(255);

        return $sizeCode . $packet;
    }

    public static function decode()
    {

    }
}
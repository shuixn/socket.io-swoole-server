<?php
declare(strict_types=1);

namespace SocketIO\Engine\Client;

use Swoole\Client as SwooleClient;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * Class WebSocketClient
 *
 * @package SocketIO\Engine\Client
 */
class WebSocketClient
{
    /** @var string */
    const VERSION = '0.1.4';

    /** @var int */
    const TOKEN_LENGHT = 16;

    /** @var int */
    const TYPE_ID_WELCOME = 0;

    /** @var int */
    const TYPE_ID_PREFIX = 1;

    /** @var int */
    const TYPE_ID_CALL = 2;

    /** @var int */
    const TYPE_ID_CALLRESULT = 3;

    /** @var int */
    const TYPE_ID_ERROR = 4;

    /** @var int */
    const TYPE_ID_SUBSCRIBE = 5;

    /** @var int */
    const TYPE_ID_UNSUBSCRIBE = 6;

    /** @var int */
    const TYPE_ID_PUBLISH = 7;

    /** @var int */
    const TYPE_ID_EVENT = 8;

    /** @var string */
    private $key;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $path;

    /** @var SwooleClient */
    private $socket;

    /** @var string */
    private $buffer = '';

    /** @var string */
    private $origin = '';

    /** @var bool */
    private $connected = false;

    /**
     * Client constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $path
     * @param string $origin
     */
    function __construct(string $host = '127.0.0.1', int $port = 8080, string $path = '/', string $origin = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->origin = $origin;
        $this->key = $this->generateToken(self::TOKEN_LENGHT);
    }

    /**
     * Disconnect on destruct
     */
    function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @return bool|mixed
     *
     * @throws \Exception
     */
    public function connect()
    {
        $this->socket = new SwooleClient(SWOOLE_SOCK_TCP);

        if (!$this->socket->connect($this->host, $this->port)) {
            throw new \Exception('connect failed');
        }

        $this->socket->send($this->createHeader());

        return $this->recv();
    }

    /**
     * @return SwooleClient
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Disconnect from server
     */
    public function disconnect()
    {
        $this->connected = false;
        $this->socket->close();
    }

    /**
     * @return bool|mixed
     *
     * @throws \Exception
     */
    public function recv()
    {
        $data = $this->socket->recv();
        if ($data === false) {
            throw new \Exception("Error: {$this->socket->errMsg}");
        }

        $this->buffer .= $data;
        $recv_data = $this->parseData($this->buffer);
        if ($recv_data) {
            $this->buffer = '';
            return $recv_data;
        }

        return false;
    }

    /**
     * @param string $data
     * @param string $type
     * @param bool $masked
     * @return bool|mixed
     */
    public function send(string $data, string $type = 'text', bool $masked = false)
    {
        switch($type) {
            case 'text':
                $_type = WEBSOCKET_OPCODE_TEXT;
                break;
            case 'binary':
            case 'bin':
                $_type = WEBSOCKET_OPCODE_BINARY;
                break;
            default:
                return false;
        }

        return $this->socket->send(WebSocketServer::pack($data, $_type, true, $masked));
    }

    /**
     * @param string $response
     * @return mixed
     * @throws \Exception
     */
    private function parseData(string $response)
    {
        if (!$this->connected && isset($response['Sec-Websocket-Accept'])) {
            if (base64_encode(pack('H*', sha1($this->key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))) === $response['Sec-Websocket-Accept']) {
                $this->connected = true;
            } else {
                throw new \Exception("error response key.");
            }
        }

        return WebSocketServer::unpack($response);
    }

    /**
     * @return string
     */
    private function createHeader()
    {
        $host = $this->host;
        if ($host === '127.0.0.1' || $host === '0.0.0.0') {
            $host = 'localhost';
        }

        return "GET {$this->path} HTTP/1.1" . "\r\n" .
            "Origin: {$this->origin}" . "\r\n" .
            "Host: {$host}:{$this->port}" . "\r\n" .
            "Sec-WebSocket-Key: {$this->key}" . "\r\n" .
            "User-Agent: PHPWebSocketClient/" . self::VERSION . "\r\n" .
            "Upgrade: websocket" . "\r\n" .
            "Connection: Upgrade" . "\r\n" .
            "Sec-WebSocket-Protocol: wamp" . "\r\n" .
            "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";
    }

    /**
     * Parse raw incoming data
     *
     * @param $header
     *
     * @return array
     */
    private function parseIncomingRaw($header)
    {
        $retval = array();
        $content = "";
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./',
                    function ($matches) {
                        return strtoupper($matches[0]);
                    },
                    strtolower(trim($match[1])));
                if (isset($retval[$match[1]])) {
                    $retval[$match[1]] = array($retval[$match[1]], $match[2]);
                } else {
                    $retval[$match[1]] = trim($match[2]);
                }
            } else {
                if (preg_match('!HTTP/1\.\d (\d)* .!', $field)) {
                    $retval["status"] = $field;
                } else {
                    $content .= $field . "\r\n";
                }
            }
        }
        $retval['content'] = $content;

        return $retval;
    }

    /**
     * Generate token
     *
     * @param int $length
     *
     * @return string
     */
    private function generateToken(int $length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < $length; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // Add numbers
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, self::TOKEN_LENGHT);

        return base64_encode($randomString);
    }

    /**
     * Generate token
     *
     * @param int $length
     *
     * @return string
     */
    public function generateAlphaNumToken(int $length)
    {
        $characters = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        srand((float)microtime() * 1000000);
        $token = '';
        do {
            shuffle($characters);
            $token .= $characters[mt_rand(0, (count($characters) - 1))];
        } while (strlen($token) < $length);

        return $token;
    }
}
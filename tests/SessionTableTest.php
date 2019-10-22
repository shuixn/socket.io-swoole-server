<?php

declare(strict_types=1);

namespace SocketIO;

use PHPUnit\Framework\TestCase;
use SocketIO\Storage\Table\SessionListenerTable;

/**
 * Class SessionTableTest
 *
 * @package SocketIO
 */
class SessionTableTest extends TestCase
{
    /** @var SessionListenerTable */
    private $sessionTable;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->sessionTable = SessionListenerTable::getInstance();
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->sessionTable->count());
    }

    public function testPush()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->assertEquals(true, $this->sessionTable->push($sid, $fd));
    }

    public function testPop()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->assertEquals($fd, $this->sessionTable->pop($sid));
    }

    public function testGet()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->sessionTable->push($sid, $fd);
        $this->assertEquals($fd, $this->sessionTable->get($sid));
        $this->assertEquals($fd, $this->sessionTable->get($sid));
    }
}
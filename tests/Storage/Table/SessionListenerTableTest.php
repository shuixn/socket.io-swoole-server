<?php

declare(strict_types=1);

namespace SocketIOTests\Storage\Table;

use PHPUnit\Framework\TestCase;
use SocketIO\Storage\Table\SessionListenerTable;

/**
 * Class SessionListenerTableTest
 *
 * @package SocketIO
 */
class SessionListenerTableTest extends TestCase
{
    /** @var SessionListenerTable */
    private $table;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->table = SessionListenerTable::getInstance(1);
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->table->count());
    }

    public function testPush()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->assertEquals(true, $this->table->push($sid, $fd));
    }

    public function testPop()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->assertEquals($fd, $this->table->pop($sid));
    }

    public function testGet()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->table->push($sid, $fd);
        $this->assertEquals($fd, $this->table->get($sid));
        $this->assertEquals($fd, $this->table->get($sid));
    }
}
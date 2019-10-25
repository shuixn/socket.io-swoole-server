<?php

declare(strict_types=1);

namespace SocketIOTests\Storage\Table;

use PHPUnit\Framework\TestCase;
use SocketIO\Storage\Table\ListenerSessionTable;

/**
 * Class ListenerSessionTableTest
 *
 * @package SocketIO
 */
class ListenerSessionTableTest extends TestCase
{
    /** @var ListenerSessionTable */
    private $table;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->table = ListenerSessionTable::getInstance(1, 1024);
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->table->count());
    }

    public function testPush()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->assertEquals(true, $this->table->push(strval($fd), $sid));
    }

    public function testPop()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->assertEquals($sid, $this->table->pop(strval($fd)));
    }

    public function testGet()
    {
        $sid = 'xyz';
        $fd = 1;
        $this->table->push(strval($fd), $sid);
        $this->assertEquals($sid, $this->table->get(strval($fd)));
        $this->assertEquals($sid, $this->table->get(strval($fd)));
    }
}
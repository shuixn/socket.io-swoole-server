<?php

declare(strict_types=1);

namespace SocketIOTests\Storage\Table;

use PHPUnit\Framework\TestCase;
use SocketIO\Storage\Table\ListenerTable;

/**
 * Class ListenerTableTest
 *
 * @package SocketIO
 */
class ListenerTableTest extends TestCase
{
    /** @var ListenerTable */
    private $table;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->table = ListenerTable::getInstance(1, 1024);
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->table->count());
    }

    public function testPush()
    {
        $fd = 1;
        $this->assertEquals(true, $this->table->push(strval($fd)));
    }

    public function testPop()
    {
        $fd = 1;
        $this->assertEquals(true, $this->table->pop(strval($fd)));
    }

    public function testIsExists()
    {
        $fd = 1;
        $this->table->push(strval($fd));
        $this->assertEquals(true, $this->table->isExists(strval($fd)));
    }

    public function testGetListener()
    {
        $fds = ['1'];
        $this->assertEquals($fds, $this->table->getListener());
    }
}
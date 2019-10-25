<?php

declare(strict_types=1);

namespace SocketIOTests\Storage\Table;

use PHPUnit\Framework\TestCase;
use SocketIO\Storage\Table\NamespaceSessionTable;

/**
 * Class NamespaceSessionTableTest
 *
 * @package SocketIO
 */
class NamespaceSessionTableTest extends TestCase
{
    /** @var NamespaceSessionTable */
    private $table;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->table = NamespaceSessionTable::getInstance(1, 1024);
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->table->count());
    }

    public function testPush()
    {
        $sid = 'xyz';
        $namespace = '/';
        $this->assertEquals(true, $this->table->push($namespace, $sid));
    }

    public function testPop()
    {
        $sid = 'xyz';
        $namespace = '/';
        $this->assertEquals(true, $this->table->pop($namespace, $sid));
    }

    public function testGet()
    {
        $sid = 'xyz';
        $namespace = '/';
        $this->table->push($namespace, $sid);
        $this->assertEquals([$sid], $this->table->get($namespace));
    }
}
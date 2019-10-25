<?php

declare(strict_types=1);

namespace SocketIOTests\Storage\Table;

use PHPUnit\Framework\TestCase;
use SocketIO\Storage\Table\SessionNamespaceTable;

/**
 * Class SessionNamespaceTableTest
 *
 * @package SocketIO
 */
class SessionNamespaceTableTest extends TestCase
{
    /** @var SessionNamespaceTable */
    private $table;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->table = SessionNamespaceTable::getInstance(1, 1024);
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->table->count());
    }

    public function testPush()
    {
        $sid = 'xyz';
        $namespace = '/';
        $this->assertEquals(true, $this->table->push($sid, $namespace));
    }

    public function testPop()
    {
        $sid = 'xyz';
        $namespace = '/';
        $this->assertEquals(true, $this->table->pop($sid, $namespace));
    }

    public function testGet()
    {
        $sid = 'xyz';
        $namespace = '/';
        $this->table->push($sid, $namespace);
        $this->assertEquals([$namespace], $this->table->get($sid));
    }
}
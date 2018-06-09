<?php

use PHPUnit\Framework\TestCase;
use Ackly\Health\Check\DB;

use Tests\Mocks\PDOMock;


class DBCheckTest extends TestCase
{
    public function testNewDbFromPDO()
    {
        $pdo = new PDOMock('', 'testuser', '');

        $db = new DB(['pdo' => $pdo]);

        $this->assertInstanceOf(DB::class, $db);

        $reflection = new ReflectionClass($db);
        $prop = $reflection->getProperty('instance');
        $prop->setAccessible(true);

        $this->assertEquals($pdo, $prop->getValue($db));
    }

    public function testNewDbFromDSN()
    {
        $dsn = 'mysql:host=localhost';

        $db = new DB(['dsn' => $dsn, 'user' => 'testuser', 'password' => '']);

        $this->assertInstanceOf(DB::class, $db);

        $reflection = new ReflectionClass($db);
        $prop = $reflection->getProperty('dsn');
        $prop->setAccessible(true);

        $this->assertEquals($dsn, $prop->getValue($db));
    }

    public function testNewDBFromDialect()
    {
        $db = new DB([
            'dialect' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'test',
            'user' => 'testuser',
            'password' => ''
        ]);

        $this->assertInstanceOf(DB::class, $db);

        $reflection = new ReflectionClass($db);
        $prop = $reflection->getProperty('dsn');
        $prop->setAccessible(true);

        $expected = 'mysql:dbname=test;host=localhost;port=3306';

        $this->assertEquals($expected, $prop->getValue($db));
    }

    public function testRunOk()
    {
        $mock = new PDOMock('', 'testuser', '');

        $db = new DB(['pdo' => $mock]);

        $res = $db->run();

        $this->assertEquals('ok', $res->status);
        $this->assertArrayHasKey('version', $res->toArray()['info']);
    }

    public function testRunFailsConnection()
    {
        $db = new DB([
            'host' => 'localhost',
            'dbname' => 'test',
            'user' => 'otheruser',
            'password' => ''
        ]);

        $res = $db->run();

        $this->assertEquals('error', $res->status);
        $this->assertEquals('error', $res->toArray()['errors'][0]['severity']);
    }

    public function testRunFailsQuery()
    {
        $mock = new PDOMock('', 'testuser', '');
        $mock->emulate_query_false = true;

        $db = new DB(['pdo' => $mock]);

        $res = $db->run();

        $this->assertEquals('error', $res->status);
        $this->assertEquals('error', $res->toArray()['errors'][0]['severity']);
        $this->assertContains('query failed', $res->toArray()['errors'][0]['message']);
    }
}
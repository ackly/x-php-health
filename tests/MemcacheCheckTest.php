<?php

use PHPUnit\Framework\TestCase;
use Ackly\Health\Check\Memcache;

use Tests\Mocks\MemcacheMock;


class MemcacheCheckTest extends TestCase
{
    public function testNewFromInstance()
    {
        $instance = new MemcacheMock();

        $res = new Memcache(['instance' => $instance]);

        $this->assertInstanceOf(Memcache::class, $res);

        $reflection = new ReflectionClass($res);
        $prop = $reflection->getProperty('instance');
        $prop->setAccessible(true);

        $this->assertInstanceOf(MemcacheMock::class, $prop->getValue($res));
    }

    public function testNewFromHostAndPort()
    {
        $res = new Memcache([
            'host' => '0.0.0.0',
            'port' => 11211,
            'class' => MemcacheMock::class
        ]);

        $this->assertInstanceOf(Memcache::class, $res);

        $reflection = new ReflectionClass($res);
        $prop = $reflection->getProperty('cacheClass');
        $prop->setAccessible(true);

        $this->assertEquals(MemcacheMock::class, $prop->getValue($res));
    }

    public function testRunOk()
    {
        $instance = new Memcache([
            'host' => 'localhost',
            'port' => 11211,
            'class' => MemcacheMock::class
        ]);

        $res = $instance->run();

        $this->assertEquals('ok', $res->status);
        $this->assertCount(0, $res->toArray()['errors']);
        $this->assertGreaterThan(0, count($res->toArray()['info']));
    }

    public function testMemcacheRaisesClass()
    {
        $cache = new MemcacheMock();
        $cache->raise_on_stats = true;

        $instance = new Memcache([
            'instance' => $cache
        ]);

        $res = $instance->run();

        $this->assertEquals('error', $res->status);
        $this->assertEquals('error', $res->toArray()['errors'][0]['severity']);
    }

    public function testRunFailed()
    {
        $instance = new Memcache([
            'host' => '0.0.0.0',
            'port' => 11211,
            'class' => MemcacheMock::class
        ]);

        $res = $instance->run();

        $this->assertEquals('error', $res->status);
        $this->assertEquals('error', $res->toArray()['errors'][0]['severity']);
    }

    public function testThresholdsMisses()
    {
        $cache = new MemcacheMock();
        $cache->addServer('localhost', 11211);

        $cache->stats = [
            'get_hits' => 2,
            'cmd_get' => 10
        ];

        $instance = new Memcache([
            'instance' => $cache,
            'thresholds' => [
                'percent_misses' => 50
            ]
        ]);

        $res = $instance->run();

        $this->assertEquals('warning', $res->status);
        $this->assertEquals('warning', $res->toArray()['errors'][0]['severity']);
    }

    public function testThresholdsEvictions()
    {
        $cache = new MemcacheMock();
        $cache->addServer('localhost', 11211);

        $cache->stats = [
            'evictions' => 100
        ];

        $instance = new Memcache([
            'instance' => $cache,
            'thresholds' => [
                'evictions' => 50
            ]
        ]);

        $res = $instance->run();

        $this->assertEquals('warning', $res->status);
        $this->assertEquals('warning', $res->toArray()['errors'][0]['severity']);
    }
}
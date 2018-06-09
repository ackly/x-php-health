<?php

use PHPUnit\Framework\TestCase;
use Ackly\Health\HealthCheck;

use Tests\Mocks\MemcacheMock;


class TestFormatter implements \Ackly\Health\Formatter\IFormatter
{
    public function format(array $result): string
    {
        return 'ok';
    }
}

class InvalidCheck {

}


class HealthCheckTest extends TestCase
{
    public function testNewHealthCheck()
    {
        $check = new HealthCheck('test');

        $this->assertInstanceOf(HealthCheck::class, $check);

        $reflection = new ReflectionClass($check);
        $attr = $reflection->getProperty('name');
        $attr->setAccessible(true);

        $this->assertEquals('test', $attr->getValue($check));
    }

    public function testAddDependency()
    {
        $check = new HealthCheck('test');

        $check->addDependency('some-service');

        $reflection = new ReflectionClass($check);
        $attr = $reflection->getProperty('dependencies');
        $attr->setAccessible(true);

        $this->assertEquals(['some-service'], $attr->getValue($check));
    }

    public function testAddDependencyArray()
    {
        $check = new HealthCheck('test');

        $check->addDependency(['serv1', 'serv2']);

        $reflection = new ReflectionClass($check);
        $attr = $reflection->getProperty('dependencies');
        $attr->setAccessible(true);

        $this->assertEquals(['serv1', 'serv2'], $attr->getValue($check));
    }

    public function testSetFormatter()
    {
        $check = new HealthCheck('test');

        $check->setFormatter(TestFormatter::class);

        $reflection = new ReflectionClass($check);
        $attr = $reflection->getProperty('formatter');
        $attr->setAccessible(true);

        $this->assertEquals(TestFormatter::class, $attr->getValue($check));
    }

    public function testAddCheck()
    {
        $check = new HealthCheck('test');

        $dummyCheck = function() {};

        $check->addCheck('test', $dummyCheck);

        $reflection = new ReflectionClass($check);
        $attr = $reflection->getProperty('checks');
        $attr->setAccessible(true);

        $this->assertArrayHasKey('test', $attr->getValue($check));
        $this->assertEquals($dummyCheck, $attr->getValue($check)['test']);
    }

    public function testAddCheckNameConflict()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Health check with name test already registered');

        $check = new HealthCheck('test');

        $check->addCheck('test', function(){});
        $check->addCheck('test', function(){});
    }

    public function testAddCheckInvalidObject()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Health check must be callable object or class name');

        $check = new HealthCheck('test');

        $check->addCheck('test', '123123');
    }

    public function testEmptyRun()
    {
        $check = new HealthCheck('test');

        $res = $check->run([]);

        $this->assertTrue(is_string($res));
    }

    public function testRunAccessibleResult()
    {
        $check = new HealthCheck('test');

        $check->run([]);

        $this->assertTrue(is_array($check->result));
        $this->assertEquals('ok', $check->result['status']);
        $this->assertEquals('test', $check->result['name']);
    }

    public function testRunReturnsDependencies()
    {
        $check = new HealthCheck('test');

        $check->addDependency(['serv1', 'serv2']);

        $check->run();

        $this->assertArrayHasKey('dependencies', $check->result);
        $this->assertEquals(['serv1', 'serv2'], $check->result['dependencies']);
    }

    public function testRunUsesFormatter()
    {
        $check = new HealthCheck('test');

        $check->setFormatter(TestFormatter::class);

        $res = $check->run();

        $this->assertEquals($res, 'ok');
    }

    public function testRunChecksComponents()
    {
        $check = new HealthCheck('test');

        $check->addCheck('test', function() {
            return new \Ackly\Health\CheckResult();
        });

        $check->run();

        $this->assertArrayHasKey('components', $check->result);
        $this->assertArrayHasKey('test', $check->result['components']);

        $this->assertEquals('ok', $check->result['components']['test']['status']);
    }

    public function testRunValidatesResult()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Each health check must return instance of Ackly\\Health\\CheckResult');

        $check = new HealthCheck('test');

        $check->addCheck('test', function() {
            return '123';
        });

        $check->run();
    }

    public function testRunValidatesCheckClass()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test check must be derived from Ackly\\Health\\Check\\BaseCheck');

        $check = new HealthCheck('test');

        $check->addCheck('test', InvalidCheck::class);

        $check->run();
    }

    public function testRunPassesArgs()
    {
        $check = new HealthCheck('test');

        $check->addCheck('test', function($arg) {
            if (!$arg) {
                throw new \Exception('Argument was not passed');
            }

            return new \Ackly\Health\CheckResult();
        });

        $check->run([
            'test' => [123]
        ]);

        $this->assertEquals('ok', $check->result['status']);
    }

    public function testComplexRun()
    {
        $check = new HealthCheck('test');

        $check->addCheck('cache', \Ackly\Health\Check\Memcache::class);

        $check->run([
            'cache' => [
                'class' => MemcacheMock::class,
                'host' => 'localhost',
                'port' => 11211
            ]
        ]);

        $this->assertArrayHasKey('cache', $check->result['components']);
        $this->assertEquals('ok', $check->result['components']['cache']['status']);
    }

    public function testRunFailsIfComponentFails()
    {
        $check = new HealthCheck('test');

        $check->addCheck('cache', \Ackly\Health\Check\Memcache::class);

        $check->run([
            'cache' => [
                'class' => MemcacheMock::class,
                'host' => 'localhost',
                'port' => 11311
            ]
        ]);

        $this->assertArrayHasKey('cache', $check->result['components']);
        $this->assertEquals('error', $check->result['components']['cache']['status']);
        $this->assertEquals('error', $check->result['status']);
    }

    public function testRunResultChangesStatus()
    {
        $check = new HealthCheck('test');

        $check->addCheck('test', function() {
            $res = new \Ackly\Health\CheckResult();

            $res->warning('some warnings');

            return $res;
        });

        $check->run();

        $this->assertArrayHasKey('test', $check->result['components']);
        $this->assertEquals('warning', $check->result['components']['test']['status']);
        $this->assertEquals('warning', $check->result['status']);
    }
}
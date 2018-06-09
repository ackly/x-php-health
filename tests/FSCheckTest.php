<?php

use PHPUnit\Framework\TestCase;
use Ackly\Health\Check\FS;


class FSCheckTest extends TestCase
{
    const TEST_DIR = '/tmp/_x_php_health_test_dir';

    public function setUp(): void
    {
        mkdir(self::TEST_DIR);
    }

    public function tearDown(): void
    {
        if (is_dir(self::TEST_DIR)) {
            rmdir(self::TEST_DIR);
        }
    }

    /**
     * @throws Exception
     */
    public function testNewFsCheck()
    {
        $instance = new FS(['path' => self::TEST_DIR]);

        $this->assertInstanceOf(FS::class, $instance);
    }

    /**
     * @throws Exception
     */
    public function testValidateNewFsCheck()
    {
        $this->expectException('Exception');

        $instance = new FS([]);
    }

    /**
     * @throws Exception
     */
    public function testRunThrowsWithInvalidPath()
    {
        $fs = new FS(['path' => '/some/nonexisting/dir']);

        $res = $fs->run();

        $this->assertEquals('error', $res->status);
        $this->assertEquals('error', $res->toArray()['errors'][0]['severity']);
    }

    /**
     * @throws Exception
     */
    public function testRunThrowsWithNonWritablePath()
    {
        chmod(self::TEST_DIR, 0100);

        $fs = new FS(['path' => self::TEST_DIR]);

        $res = $fs->run();

        $this->assertEquals('error', $res->status);
        $this->assertEquals('error', $res->toArray()['errors'][0]['severity']);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testFormatBytes()
    {
        $reflection = new ReflectionClass(FS::class);
        $method = $reflection->getMethod('formatBytes');
        $method->setAccessible(true);

        $fs = new FS(['path' => self::TEST_DIR]);

        $res = $method->invokeArgs($fs, [2048]);

        $this->assertEquals('2.00KB', $res);

        $res = $method->invokeArgs($fs, [3350074490]);
        $this->assertEquals('3.12GB', $res);
    }

    /**
     * @throws Exception
     */
    public function testRun()
    {
        $fs = new FS(['path' => self::TEST_DIR]);

        $res = $fs->run();

        $this->assertInstanceOf(\Ackly\Health\CheckResult::class, $res);

        $this->assertEquals('ok', $res->status);
    }

    /**
     * @throws Exception
     */
    public function testRunThresholdsFreeBytes()
    {
        $fs = new FS(['path' => self::TEST_DIR, 'thresholds' => [
            'free_bytes' => pow(1024, 5)             // 1TB
        ]]);

        $res = $fs->run();

        $this->assertEquals('warning', $res->status);

        $this->assertEquals('warning', $res->toArray()['errors'][0]['severity']);
    }

    /**
     * @throws Exception
     */
    public function testRunThresholdsUsagePercent()
    {
        $fs = new FS(['path' => self::TEST_DIR, 'thresholds' => [
            'usage_percent' => 1
        ]]);

        $res = $fs->run();

        $this->assertEquals('warning', $res->status);

        $this->assertEquals('warning', $res->toArray()['errors'][0]['severity']);
    }
}
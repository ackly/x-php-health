<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ackly\Health\CheckResult;


final class CheckResultTest extends TestCase
{
    public function testSetError()
    {
        $result = new CheckResult();

        $result->error('test error');

        $this->assertEquals('error', $result->status);

        $arr = $result->toArray();

        $this->assertArrayHasKey('errors', $arr);
        $this->assertArrayHasKey(0, $arr['errors']);
        $this->assertEquals(['severity' => 'error', 'message' => 'test error'], $arr['errors'][0]);
    }

    public function testSetWarning()
    {
        $result = new CheckResult();

        $result->warning('test warning');

        $this->assertEquals('warning', $result->status);

        $arr = $result->toArray();

        $this->assertArrayHasKey('errors', $arr);
        $this->assertArrayHasKey(0, $arr['errors']);
        $this->assertEquals(['severity' => 'warning', 'message' => 'test warning'], $arr['errors'][0]);
    }

    public function testSetInfo()
    {
        $result = new CheckResult();

        $result->info('key', 'value');

        $this->assertEquals('ok', $result->status);

        $arr = $result->toArray();

        $this->assertArrayHasKey('info', $arr);
        $this->assertEquals(['key' => 'value'], $arr['info']);
    }
}
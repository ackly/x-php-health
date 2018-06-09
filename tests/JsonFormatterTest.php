<?php

use PHPUnit\Framework\TestCase;
use Ackly\Health\Formatter\JsonFormatter;


final class JsonFormatterTest extends TestCase
{
    public function testFormat()
    {
        $src = ['a' => ['b' => 1], 'c' => 0.22, 'd' => [1, 2, 3]];

        $formatter = new JsonFormatter();

        $expected = '{"a":{"b":1},"c":0.22,"d":[1,2,3]}';

        $this->assertEquals($expected, $formatter->format($src));
    }
}
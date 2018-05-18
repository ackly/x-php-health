<?php

namespace Ackly\Health\Check;


use Ackly\Health\CheckResult;

/**
 * Class BaseCheck
 */
abstract class BaseCheck
{
    /**
     * Run service check
     *
     * @return mixed
     */
    abstract public function run(): CheckResult;
}
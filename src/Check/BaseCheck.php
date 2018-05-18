<?php

namespace Vsemayki\Health\Check;


use Vsemayki\Health\CheckResult;

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
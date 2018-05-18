<?php

namespace Ackly\Health\Formatter;


/**
 * Interface IFormatter
 *
 * @package Ackly\Health\Formatter
 */
interface IFormatter
{
    /**
     *
     *
     * @param array $result
     *
     * @return string
     */
    public function format(array $result): string;
}
<?php

namespace Ackly\Health\Formatter;


/**
 * Class JsonFormatter
 *
 * @package Ackly\Health\Formatter
 */
class JsonFormatter implements IFormatter
{
    /**
     * @param array $result
     *
     * @return string
     */
    public function format(array $result): string
    {
        return json_encode($result);
    }
}
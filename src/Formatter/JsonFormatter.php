<?php

namespace Vsemayki\Health\Formatter;


/**
 * Class JsonFormatter
 *
 * @package Vsemayki\Health\Formatter
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
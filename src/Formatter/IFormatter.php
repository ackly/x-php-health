<?php

namespace Vsemayki\Health\Formatter;


/**
 * Interface IFormatter
 *
 * @package Vsemayki\Health\Formatter
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
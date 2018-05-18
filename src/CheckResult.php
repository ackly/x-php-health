<?php

namespace Ackly\Health;

/**
 * Class CheckResult
 *
 * Representing health check result
 *
 * @package Ackly\Health
 */
class CheckResult
{
    public $status = 'ok';

    protected $errors = [];
    protected $warnings = [];
    protected $info = [];

    /**
     * Add error message
     *
     * @param string $message
     */
    public function error(string $message)
    {
        $this->errors[] = $message;
    }

    /**
     * Add warning message
     *
     * @param string $message
     */
    public function warning(string $message)
    {
        $this->warnings[] = $message;
    }

    /**
     * Add information message
     *
     * @param string $key
     * @param string $message
     */
    public function info(string $key, string $message)
    {
        $this->info[$key] = $message;
    }

    /**
     * Convert check result to array
     *
     * @return array
     */
    public function toArray(): array
    {
        if (!empty($this->warnings)) {
            $this->status = 'warning';
        } elseif (!empty($this->errors)) {
            $this->status = 'error';
        }

        return [
            'status' => $this->status,
            'errors' => array_merge(array_map(function($x) {
                return ['severity' => 'error', 'message' => $x];
            }, $this->errors), array_map(function($x) {
                return ['severity' => 'warning', 'message' => $x];
            }, $this->warnings)),
            'info' => $this->info
        ];
    }
}
<?php

namespace Tests\Mocks;

/**
 * Class MemcacheMock
 *
 * Mock for native \Memcache(d) object
 */
class MemcacheMock
{
    protected $servers = [];

    public $return_stats = true;
    public $return_stats_empty = false;
    public $raise_on_stats = false;

    public $stats = [];

    public $validServer = 'localhost:11211';

    public function addServer($host, $port=11211, ...$extra)
    {
        $this->servers[] = "$host:$port";
    }

    public function getStats(...$args)
    {
        if ($this->raise_on_stats) {
            throw new \Exception('Memcache exception');
        }

        if (!$this->return_stats) {
            return false;
        }

        if ($this->return_stats_empty) {
            return [];
        }

        $stats = [];

        foreach ($this->servers as $server) {
            if ($server !== $this->validServer) {
                return false;
            }

            $stats[$server] = array_merge($this->defaultStats(), $this->stats);
        }

        return $stats;
    }

    private function defaultStats()
    {
        return [
            'pid' => 1,
            'uptime' => 100,
            'time' => time(),
            'version' => '1.1.1',
            'rusage_user' => 0,
            'rusage_system' => 0,
            'curr_items' => 0,
            'total_items' => 0,
            'bytes' => 0,
            'evictions' => 0,
            'curr_connections' => 0,
            'total_connections' => 0,
            'connection_structures' => 0,
            'cmd_get' => 0,
            'cmd_set' => 0,
            'get_hits' => 0,
            'get_misses' => 0,
            'bytes_read' => 0,
            'bytes_written' => 0,
            'limit_maxbytes' => 0
        ];
    }
}
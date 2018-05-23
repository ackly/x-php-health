<?php

namespace Ackly\Health\Check;

use Ackly\Health\CheckResult;

/**
 * Class Memcache
 *
 * Checks availability and status of memcache.
 *
 * @package Ackly\Health\Check
 */
class Memcache extends BaseCheck
{
    const DEFAULT_CACHE_CLASS = 'Memcache';

    protected $cacheClass;
    protected $host = 'localhost';
    protected $port = '11211';

    protected $instance = null;

    protected $thresholds = [];

    /**
     * Memcache constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['instance'])) {
            $this->instance = $options['instance'];
        } else {
            $this->cacheClass = $options['class'] ?? self::DEFAULT_CACHE_CLASS;

            if (isset($options['host'])) {
                $this->host = $options['host'];
            }

            if (isset($options['port'])) {
                $this->port = $options['port'];
            }
        }

        if (isset($options['thresholds'])) {
            $this->thresholds = $options['thresholds'];
        }
    }

    /**
     * @inheritdoc
     */
    public function run(): CheckResult
    {
        /** @var Memcache|\Memcached $instance */
        $instance = $this->instance;
        $result = new CheckResult();

        try {
            if (!$instance) {
                $instance = new $this->cacheClass();

                $instance->addServer($this->host, $this->port);
            }

            $statResult = $instance->getStats();

            if ($statResult || !$stats = current($statResult)) {
                $result->error('Failed to receive stats (Connection failed).');
            } else {
                $result->info('version', $stats['version']);
                $result->info('uptime', $stats['uptime']);
                $result->info('curr_items', $stats['curr_items']);
                $result->info('total_items', $stats['total_items']);
                $result->info('evictions', $stats['evictions']);

                if ($stats['cmd_get'] > 0) {
                    $percentGet = round((float)$stats['get_hits'] / (float)$stats['cmd_get'] * 100, 3);
                    $percentMisses = 100 - $percentGet;
                } else {
                    $percentGet = 0;
                    $percentMisses = 0;
                }

                $result->info('percent_get', $percentGet);
                $result->info('percent_misses', $percentMisses);

                if (isset($this->thresholds['percent_misses']) && (float)$this->thresholds['percent_misses'] < $percentMisses) {
                    $result->warning('Number of misses overpasses specified threshold (' . $this->thresholds['percent_misses'] . ')');
                }

                if (isset($this->thresholds['evictions']) && (float)$this->thresholds['evictions'] < $stats['evictions']) {
                    $result->warning('Number of evictions overpasses specified threshold (' . $this->thresholds['evictions'] . ')');
                }
            }
        } catch (\Exception $e) {
            $result->error($e->getMessage());
        }

        return $result;
    }
}
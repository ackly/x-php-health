<?php

namespace Ackly\Health\Check;

use Ackly\Health\CheckResult;

/**
 * Class FS
 *
 * Checks status of specified path on file system
 *
 * @package Ackly\Health\Check
 */
class FS extends BaseCheck
{
    protected $path = null;

    protected $thresholds = [];

    /**
     * FS constructor.
     *
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['path'])) {
            throw new \Exception('path option required for fs check');
        }

        $this->path = $options['path'];

        if (isset($options['thresholds'])) {
            $this->thresholds = $options['thresholds'];
        }
    }

    /**
     * @inheritdoc
     */
    public function run(): CheckResult
    {
        $result = new CheckResult();

        $result->info('path', $this->path);

        try {
            if (!is_dir($this->path)) {
                throw new \Exception('Directory does not exist');
            }

            if (!is_writable($this->path)) {
                throw new \Exception('Directory is not available for writing');
            }

            $freeSpace = disk_free_space($this->path);

            $result->info('free space', $this->formatBytes($freeSpace));

            $totalSpace = disk_total_space($this->path);

            $usagePercent = round($freeSpace / $totalSpace * 100, 3);

            $result->info('usage', $usagePercent . '%');

            if (isset($this->thresholds['free_bytes']) && $this->thresholds['free_bytes'] > $freeSpace) {
                $result->warning('Amount of free disk space is less that required value (' . $this->formatBytes($this->thresholds['free_bytes']) . ')');
            }

            if (isset($this->thresholds['usage_percent']) && $this->thresholds['usage_percent'] < $usagePercent) {
                $result->warning('Disk usage overpasses required threshold (' . $this->thresholds['usage_percent'] . ')');
            }
        } catch (\Exception $e) {
            $result->error($e->getMessage());
        }

        return $result;
    }

    /**
     * Format bytes to human readable format
     *
     * @param number $bytes
     *
     * @return string
     */
    protected function formatBytes($bytes): string
    {
        $suffix = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $p = min((int)log($bytes, 1024), count($suffix) - 1);

        return sprintf('%1.2f%s', ($bytes / pow(1024, $p)), $suffix[$p]);
    }
}
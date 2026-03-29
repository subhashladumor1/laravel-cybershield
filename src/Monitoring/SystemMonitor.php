<?php

namespace CyberShield\Monitoring;

use CyberShield\Models\SystemMetric;

class SystemMonitor
{
    /**
     * Capture and store current system metrics.
     */
    public function captureMetrics(): void
    {
        SystemMetric::create([
            'cpu_load' => $this->getCpuLoad(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'captured_at' => now(),
        ]);
    }

    /**
     * Get CPU load percentage.
     */
    protected function getCpuLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return (float) ($load[0] * 100 / $this->getCpuCount());
        }

        return 0.0;
    }

    /**
     * Get memory usage percentage.
     */
    protected function getMemoryUsage(): float
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $free = shell_exec('free');
            $free = (string) trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_values($mem);
            return (float) round($mem[2] / $mem[1] * 100, 2);
        }

        return 0.0;
    }

    /**
     * Get disk usage percentage.
     */
    protected function getDiskUsage(): float
    {
        $path = base_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return (float) round($used / $total * 100, 2);
    }

    /**
     * Get number of CPU cores.
     */
    protected function getCpuCount(): int
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $numCpus = (int) shell_exec('nproc');
            return $numCpus > 0 ? $numCpus : 1;
        }

        return 1;
    }
}

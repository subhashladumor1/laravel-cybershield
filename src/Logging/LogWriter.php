<?php

namespace CyberShield\Logging;

use Illuminate\Support\Facades\File;

class LogWriter
{
    /**
     * Write the formatted log to the filesystem.
     *
     * @param string $channel
     * @param string $content
     * @return void
     */
    public function write(string $channel, string $content): void
    {
        $logPath = storage_path("logs/cybershield/{$channel}.log");
        $logDir = dirname($logPath);

        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0755, true);
        }

        $this->rotateIfNecessary($logPath);

        File::append($logPath, $content . PHP_EOL);
    }

    /**
     * Perform rotation if the file exceeds the maximum size or daily rotation is enabled.
     *
     * @param string $logPath
     * @return void
     */
    protected function rotateIfNecessary(string $logPath): void
    {
        if (!File::exists($logPath)) {
            return;
        }

        $rotation = config('cybershield.logging.rotation', 'daily');
        $maxSize = config('cybershield.logging.max_size', 5242880); // 5MB

        // Daily rotation
        if ($rotation === 'daily') {
            $lastModifiedDate = date('Y-m-d', File::lastModified($logPath));
            if ($lastModifiedDate !== date('Y-m-d')) {
                $this->rotate($logPath);
                return;
            }
        }

        // Size rotation
        if (File::size($logPath) >= $maxSize) {
            $this->rotate($logPath);
        }
    }

    /**
     * Rename the existing log file for rotation.
     *
     * @param string $logPath
     * @return void
     */
    protected function rotate(string $logPath): void
    {
        $timestamp = date('Y-m-d_H-i-s');
        $info = pathinfo($logPath);
        $newPath = "{$info['dirname']}/{$info['filename']}_{$timestamp}.log";
        
        File::move($logPath, $newPath);
    }
}

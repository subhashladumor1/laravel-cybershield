<?php

namespace CyberShield\Logging;

class SecurityLogger
{
    /**
     * Log a security event.
     *
     * @param string $channel
     * @param array $data
     * @param string $level
     * @return void
     */
    public static function log(string $channel, array $data = [], string $level = 'info'): void
    {
        if (!config('cybershield.logging.enabled', true)) {
            return;
        }

        app(LogManager::class)->handle($channel, $data, $level);
    }

    /**
     * Helper methods for different levels.
     */
    public static function info(string $channel, array $data = []): void
    {
        static::log($channel, $data, 'info');
    }

    public static function warning(string $channel, array $data = []): void
    {
        static::log($channel, $data, 'warning');
    }

    public static function alert(string $channel, array $data = []): void
    {
        static::log($channel, $data, 'alert');
    }

    public static function critical(string $channel, array $data = []): void
    {
        static::log($channel, $data, 'critical');
    }
}

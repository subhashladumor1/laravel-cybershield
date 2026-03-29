<?php

namespace CyberShield\Logging;

class LogChannelResolver
{
    /**
     * Check if a log channel is enabled.
     *
     * @param string $channel
     * @return bool
     */
    public function isEnabled(string $channel): bool
    {
        $channels = config('cybershield.logging.channels', []);
        
        return isset($channels[$channel]) && $channels[$channel] === true;
    }

    /**
     * Get all currently supported channels.
     *
     * @return array
     */
    public function getSupportedChannels(): array
    {
        return [
            'request',
            'api',
            'bot',
            'threat',
            'system',
            'traffic',
            'database',
            'queue',
            'middleware',
        ];
    }
}

<?php

namespace CyberShield\Logging;

class LogFormatter
{
    /**
     * Format the log data based on the configured template.
     *
     * @param array $context
     * @param string $level
     * @return string
     */
    public function format(array $context, string $level): string
    {
        $template = config('cybershield.logging.format', '[{datetime}] {level} {ip} {user_id} {method} {url} {status} {message}');
        
        $placeholders = [
            '{datetime}' => $context['timestamp'] ?? now()->toDateTimeString(),
            '{level}' => strtoupper($level),
            '{ip}' => $context['ip'] ?? 'N/A',
            '{method}' => $context['method'] ?? 'N/A',
            '{url}' => $context['url'] ?? 'N/A',
            '{status}' => $context['status'] ?? 'N/A',
            '{message}' => $context['message'] ?? '',
            '{user_id}' => $context['user_id'] ?? 'Guest',
            '{user_agent}' => $context['user_agent'] ?? 'N/A',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
}

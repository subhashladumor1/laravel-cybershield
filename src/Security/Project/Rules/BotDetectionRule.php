<?php

namespace CyberShield\Security\Project\Rules;

class BotDetectionRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'Bot Vulnerability Check';
    }

    public function getDescription(): string
    {
        return 'Scans for patterns related to bot activity and protection bypass.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            'User-Agent' => 'Manual User-Agent handling detected, check for spoofing',
            'curl_exec' => 'Outgoing request found, possible scraping behavior',
            'GuzzleHttp' => 'Guzzle client usage, check for bot protection settings',
        ];

        return $this->scanFiles($patterns, ['app'], $basePath);
    }
}


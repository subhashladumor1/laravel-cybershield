<?php

namespace CyberShield\Security\Project\Rules;

use CyberShield\Security\Project\Contracts\ScannerRuleInterface;

class ConfigRule implements ScannerRuleInterface
{
    public function getName(): string
    {
        return 'Configuration Audit';
    }

    public function getDescription(): string
    {
        return 'Audits environment and application settings for security risks.';
    }

    public function scan(?string $basePath = null): array
    {
        $findings = [];

        if (config('app.debug') === true && app()->environment('production')) {
            $findings[] = ['message' => 'APP_DEBUG is enabled in production environment', 'severity' => 'critical'];
        }

        if (empty(config('app.key'))) {
            $findings[] = ['message' => 'APP_KEY is not set', 'severity' => 'critical'];
        }

        if (config('session.driver') === 'cookie' && !config('session.encrypt')) {
            $findings[] = ['message' => 'Session driver is set to cookie without encryption', 'severity' => 'high'];
        }
        
        if (config('session.secure') === false && !app()->environment('local')) {
            $findings[] = ['message' => 'Session secure attribute is disabled in non-local environment', 'severity' => 'high'];
        }

        return $findings;
    }
}


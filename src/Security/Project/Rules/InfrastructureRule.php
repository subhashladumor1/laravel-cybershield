<?php

namespace CyberShield\Security\Project\Rules;

class InfrastructureRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'Infrastructure Check';
    }

    public function getDescription(): string
    {
        return 'Audits infrastructure-related settings and php.ini risks.';
    }

    public function scan(?string $basePath = null): array
    {
        $findings = [];

        // Dynamic checks for php settings if possible, or patterns in config
        $patterns = [
            'allow_url_fopen' => 'Potentially dangerous php setting: allow_url_fopen',
            'display_errors' => 'Insecure setting: display_errors should be off in production',
            '3306' => 'Hardcoded MySQL port 3306 detected in config',
        ];

        return $this->scanFiles($patterns, ['config'], $basePath);
    }
}


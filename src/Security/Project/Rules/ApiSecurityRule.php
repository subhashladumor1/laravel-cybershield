<?php

namespace CyberShield\Security\Project\Rules;

class ApiSecurityRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'API Security Scan';
    }

    public function getDescription(): string
    {
        return 'Identifies potential API security risks and data exposure.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            'Resource::collection' => 'API Resource collection usage, verify data exposure',
            'api_token' => 'Usage of api_token detected, check for secure hashing',
            '\$request->all\(\)' => 'Broad request parameter usage in API controller',
        ];

        return $this->scanFiles($patterns, ['app/Http/Resources', 'app/Http/Controllers/Api', 'app'], $basePath);
    }
}


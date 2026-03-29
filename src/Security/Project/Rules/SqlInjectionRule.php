<?php

namespace CyberShield\Security\Project\Rules;

class SqlInjectionRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'SQL Injection Check';
    }

    public function getDescription(): string
    {
        return 'Detects potential SQL injection vulnerabilities in raw queries.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            'DB::(select|raw|statement)\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'Raw SQL query with variable interpolation',
            '->whereRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'whereRaw() with variable interpolation',
            '->selectRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'selectRaw() with variable interpolation',
            '->orderByRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'orderByRaw() with variable interpolation',
            '->havingRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'havingRaw() with variable interpolation',
        ];

        return $this->scanFiles($patterns, ['app'], $basePath);
    }
}


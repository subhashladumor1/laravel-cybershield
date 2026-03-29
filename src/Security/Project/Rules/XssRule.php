<?php

namespace CyberShield\Security\Project\Rules;

class XssRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'XSS Protection Check';
    }

    public function getDescription(): string
    {
        return 'Scans for potential XSS vulnerabilities in Blade views and PHP scripts.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            '\{!!\s*\$[a-zA-Z0-9_]+\s*!!\}' => 'Unescaped Blade output {!! ... !!} detected',
            'echo\s+\$[a-zA-Z0-9_]+' => 'Native PHP echo without escaping',
            'print\s+\$[a-zA-Z0-9_]+' => 'Native PHP print without escaping',
        ];

        return $this->scanFiles($patterns, ['resources/views', 'app'], $basePath);
    }
}


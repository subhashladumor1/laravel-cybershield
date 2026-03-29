<?php

namespace CyberShield\Security\Project\Rules;

class FileUploadRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'File Upload Security';
    }

    public function getDescription(): string
    {
        return 'Detects insecure file upload practices.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            '->move\s*\(' => 'Direct move() of uploaded file without explicit validation check',
            '->storeAs\s*\(\s*.*?\.[a-z0-9]+["\']' => 'Fixed extension in storeAs() might be bypassable',
        ];

        return $this->scanFiles($patterns, ['app/Http/Controllers', 'app'], $basePath);
    }
}


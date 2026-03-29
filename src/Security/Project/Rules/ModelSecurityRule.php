<?php

namespace CyberShield\Security\Project\Rules;

class ModelSecurityRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'Data Models Check';
    }

    public function getDescription(): string
    {
        return 'Scans models for mass assignment risks and exposure.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            'protected\s+\$guarded\s*=\s*\[\s*\]' => 'Model allows all fields to be mass-assigned ($guarded = [])',
            'protected\s+\$fillable\s*=\s*\[\s*["\']\*["\']\s*\]' => 'Model allows all fields to be mass-assigned ($fillable = ["*"])',
            'protected\s+\$hidden\s*=\s*\[\s*\]' => 'Potential sensitive data exposure in model ($hidden is empty)',
        ];

        return $this->scanFiles($patterns, ['app/Models', 'app'], $basePath);
    }
}


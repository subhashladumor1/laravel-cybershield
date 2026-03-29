<?php

namespace CyberShield\Security\Project\Rules;

use CyberShield\Security\Project\Contracts\ScannerRuleInterface;
use Illuminate\Support\Facades\File;

class DependencyRule implements ScannerRuleInterface
{
    public function getName(): string
    {
        return 'Dependency Audit';
    }

    public function getDescription(): string
    {
        return 'Checks composer.json for risky version constraints.';
    }

    public function scan(?string $basePath = null): array
    {
        $findings = [];
        $composerPath = ($basePath ?: base_path()) . DIRECTORY_SEPARATOR . 'composer.json';

        if (File::exists($composerPath)) {
            $content = File::get($composerPath);
            if (str_contains($content, '"*": "*"')) {
                $findings[] = ['message' => 'Wildcard dependency versions detected in composer.json', 'severity' => 'medium'];
            }
        }

        return $findings;
    }
}


<?php

namespace CyberShield\Security\Project\Rules;

use CyberShield\Security\Project\Contracts\ScannerRuleInterface;
use Illuminate\Support\Facades\File;

abstract class AbstractFileScannerRule implements ScannerRuleInterface
{
    /**
     * Helper to scan files in directories for patterns.
     */
    protected function scanFiles(array $patterns, array $directories, ?string $basePath = null)
    {
        $findings = [];
        $basePath = $basePath ?: base_path();

        foreach ($directories as $dir) {
            $path = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
            
            if (!File::isDirectory($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php' && $file->getExtension() !== 'blade.php') {
                    continue;
                }

                $content = File::get($file->getRealPath());
                foreach ($patterns as $pattern => $message) {
                    if (preg_match('/' . $pattern . '/i', $content)) {
                        $findings[] = [
                            'message' => $message,
                            'file' => $file->getRelativePathname(),
                            'severity' => 'medium'
                        ];
                    }
                }
            }
        }

        return $findings;
    }
}


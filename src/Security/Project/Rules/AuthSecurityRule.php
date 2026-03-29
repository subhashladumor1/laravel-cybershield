<?php

namespace CyberShield\Security\Project\Rules;

class AuthSecurityRule extends AbstractFileScannerRule
{
    public function getName(): string
    {
        return 'Authentication Audit';
    }

    public function getDescription(): string
    {
        return 'Checks for insecure authentication and authorization patterns.';
    }

    public function scan(?string $basePath = null): array
    {
        $patterns = [
            'Auth::loginUsingId' => 'Dangerous usage of loginUsingId() detected',
            'password_hash' => 'Manual password hashing, use Laravel Hash or bcrypt() instead',
            'AttemptLogin' => 'Custom login logic detected, check for proper rate limiting',
        ];

        return $this->scanFiles($patterns, ['app/Http/Controllers/Auth', 'app'], $basePath);
    }
}


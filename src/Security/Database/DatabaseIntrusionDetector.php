<?php

namespace CyberShield\Security\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseIntrusionDetector
{
    public function monitor()
    {
        DB::listen(function ($query) {
            $this->analyze($query->sql, $query->bindings);
        });
    }

    protected function analyze($sql, $bindings)
    {
        // 1. Detect common injection patterns in raw queries
        if (preg_match('/(union|select).*?(from|where)/i', $sql)) {
            \CyberShield\Logging\SecurityLogger::critical('database', [
                'status' => 'threat',
                'message' => "Potential SQL injection detected: {$sql}",
            ]);
        }

        // 2. Detect sensitive data access
        if (str_contains(strtolower($sql), 'users') && str_contains(strtolower($sql), 'password')) {
            \CyberShield\Logging\SecurityLogger::warning('database', [
                'status' => 'sensitive',
                'message' => "Sensitive data access: {$sql}",
            ]);
        }
    }
}


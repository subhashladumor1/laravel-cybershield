<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CyberShield Main Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific security modules here.
    |
    */

    'enabled' => env('CYBERSHIELD_ENABLED', true),

    'modules' => [
        'request_security' => env('CYBERSHIELD_REQUEST_SECURITY_ENABLED', true),
        'rate_limiting' => env('CYBERSHIELD_RATE_LIMITING_ENABLED', true),
        'bot_protection' => env('CYBERSHIELD_BOT_PROTECTION_ENABLED', true),
        'network_security' => env('CYBERSHIELD_NETWORK_SECURITY_ENABLED', true),
        'auth_security' => env('CYBERSHIELD_AUTH_SECURITY_ENABLED', true),
        'api_security' => env('CYBERSHIELD_API_SECURITY_ENABLED', true),
        'threat_detection' => env('CYBERSHIELD_THREAT_DETECTION_ENABLED', true),
        'monitoring' => env('CYBERSHIELD_MONITORING_ENABLED', true),
    ],

    // Global toggle for middleware behavior: 'active' (blocks) or 'log' (logs only)
    'global_mode' => env('CYBERSHIELD_GLOBAL_MODE', 'active'),

    'request_security' => [
        'max_request_size' => env('CYBERSHIELD_MAX_SIZE', 5242880), // 5MB
        'enforce_https' => env('CYBERSHIELD_ENFORCE_HTTPS', true),
        'allowed_origins' => explode(',', env('CYBERSHIELD_ALLOWED_ORIGINS', 'localhost')),
        'allowed_content_types' => ['application/json', 'text/html', 'multipart/form-data'],
        'required_headers' => ['User-Agent', 'Accept'],
        'trusted_hosts' => ['localhost', '127.0.0.1'],
        'ajax_only' => false,
    ],

    'rate_limiting' => [
        'driver' => env('CYBERSHIELD_RATE_LIMIT_DRIVER', 'cache'),
        'ip_limit' => 60,
        'user_limit' => 100,
        'api_limit' => 1000,
        'burst_limit' => 10,
        'window' => 60,
    ],

    'bot_protection' => [
        'block_bots' => env('CYBERSHIELD_BLOCK_BOTS', false),
        'block_headless' => true,
        'block_scrapers' => true,
    ],

    'network_security' => [
        'block_tor' => env('CYBERSHIELD_BLOCK_TOR', false),
        'blocked_countries' => [],
        'blocked_regions' => [],
        'threat_score_threshold' => 80,
    ],

    'auth_security' => [
        'strong_password_regex' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
        'session_timeout' => 3600,
        'login_attempts_limit' => 5,
    ],

    'api_security' => [
        'enabled' => env('CYBERSHIELD_API_SECURITY_ENABLED', true),
        'keys_table' => 'api_keys',
        'signature_algo' => 'sha256',
        'timestamp_tolerance' => 60,
        'verify_signature' => env('CYBERSHIELD_API_VERIFY_SIGNATURE', true),
        'replay_protection' => env('CYBERSHIELD_API_REPLAY_PROTECTION', true),
        'default_concurrent_limit' => 10,
        'daily_cost_limit' => 10000,
        'abuse_threshold' => 100, // requests per 10s
        'auto_block' => env('CYBERSHIELD_API_AUTO_BLOCK', true),
        'endpoint_costs' => [
            'api/v1/heavy-endpoint' => 50,
            'api/v1/export' => 20,
        ],
    ],

    'threat_detection' => [
        'log_threats' => true,
        'block_on_threat' => true,
        'sql_injection' => true,
        'xss_attack' => true,
        'rce_attack' => true,
    ],

    'monitoring' => [
        'log_channel' => env('CYBERSHIELD_LOG_CHANNEL', 'stack'),
        'db_logging' => true,
        'exclude_paths' => ['/_debugbar*', '/horizon*'],
    ],

    'whitelist' => [
        '127.0.0.1',
    ],
];

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
        'enabled' => env('CYBERSHIELD_RATE_LIMITING_ENABLED', true),
        'driver' => env('CYBERSHIELD_RATE_LIMIT_DRIVER', 'cache'),
        
        'ip_limit_details' => [
            'limit' => 60,
            'window' => 60,
            'strategy' => 'linear', // linear, exponential, fibonacci
            'message' => 'Too many requests. Please slow down.',
        ],

        'login' => [
            'limit' => 5,
            'window' => 300, // 5 minutes
            'strategy' => 'fibonacci',
            'message' => 'Too many login attempts. Your access is temporarily restricted.',
        ],

        'registration' => [
            'limit' => 3,
            'window' => 3600, // 1 hour
            'strategy' => 'exponential',
            'message' => 'Too many registration attempts from this IP.',
        ],

        'api' => [
            'limit' => 1000,
            'window' => 3600,
            'strategy' => 'linear',
        ],
    ],

    'bot_protection' => [
        'enabled' => env('CYBERSHIELD_BOT_PROTECTION_ENABLED', true),
        'block_bots' => env('CYBERSHIELD_BLOCK_BOTS', false),
        'block_headless' => true,
        'block_scrapers' => true,
        'pacing_limit' => 50, // requests per window
        'pacing_window' => 10, // seconds
        'block_response_code' => 403,
        'honeypot' => [
            'enabled' => true,
            'field_name' => 'hp_token_id',
        ],
        'bots' => [
            'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
            'curl', 'python', 'postman', 'selenium', 'headless', 'phantomjs', 'gherkin',
            'scrapy', 'wget', 'urllib', 'httpclient', 'php', 'perl', 'ruby'
        ],
        'suspicious_headers' => [
            'X-Puppeteer-Request',
            'X-Selenium-Driver',
            'X-Headless-Chrome'
        ],
        'browser_common_headers' => [
            'Accept', 'Accept-Encoding', 'Accept-Language'
        ],
    ],

    'network_security' => [
        'block_tor' => env('CYBERSHIELD_BLOCK_TOR', false),
        'blocked_countries' => [],
        'blocked_regions' => [],
        'threat_score_threshold' => 80,
        'messages' => [
            'blacklisted' => 'Your IP address ({ip}) is blacklisted.',
            'blocked' => 'Your IP address ({ip}) has been blocked. Reason: {reason}',
            'geo_blocked' => 'Access denied from your location ({country}).',
            'tor_blocked' => 'Access via TOR network is not allowed.',
        ],
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
        'headers' => [
            'key' => 'X-API-KEY',
            'signature' => 'X-Signature',
            'nonce' => 'X-Nonce',
            'timestamp' => 'X-Timestamp',
        ],
        'fingerprint_headers' => [
            'User-Agent',
            'Accept-Language',
            'Accept-Encoding',
        ],
        'endpoint_costs' => [
            'api/v1/heavy-endpoint' => 50,
            'api/v1/export' => 20,
            'api/v1/search' => 5,
        ],
    ],

    'firewall' => [
        'inspection_targets' => ['query', 'body', 'headers', 'uri'],
        'blocking_ttl' => [
            'low' => 1,      // days
            'medium' => 3,   // days
            'high' => 7,     // days
            'critical' => 30 // days
        ],
    ],

    'malware_scanner' => [
        'suspicious_patterns' => [
            'eval\(', 'base64_decode\(', 'shell_exec\(', 'system\(', 'passthru\(',
            'exec\(', 'popen\(', 'proc_open\(', 'pcntl_exec\(', 'assert\(',
            'preg_replace\(.*?\/e', 'gzinflate\(', 'str_rot13\(',
        ],
        'scanned_extensions' => ['php', 'phtml', 'php3', 'php4', 'php5', 'phps'],
    ],

    'threat_detection' => [
        'log_threats' => true,
        'block_on_threat' => true,
        'sql_injection' => true,
        'xss_attack' => true,
        'rce_attack' => true,
        'traversal_attack' => true,
        'scoring' => [
            'insecure_request' => 10,
            'missing_accept_language' => 20,
            'suspicious_user_agent' => 30,
        ],
        'score_ttl' => 86400, // 24 hours
    ],

    'project_scanner' => [
        'rules' => [
            \CyberShield\Security\Project\Rules\MalwareRule::class,
            \CyberShield\Security\Project\Rules\SqlInjectionRule::class,
            \CyberShield\Security\Project\Rules\XssRule::class,
            \CyberShield\Security\Project\Rules\ConfigRule::class,
            \CyberShield\Security\Project\Rules\DependencyRule::class,
            \CyberShield\Security\Project\Rules\ModelSecurityRule::class,
            \CyberShield\Security\Project\Rules\FileUploadRule::class,
            \CyberShield\Security\Project\Rules\BotDetectionRule::class,
            \CyberShield\Security\Project\Rules\ApiSecurityRule::class,
            \CyberShield\Security\Project\Rules\AuthSecurityRule::class,
            \CyberShield\Security\Project\Rules\InfrastructureRule::class,
        ],
    ],

    'signatures' => [
        'path' => env('CYBERSHIELD_SIGNATURES_PATH', base_path('src/Signatures')),
        'custom_path' => env('CYBERSHIELD_CUSTOM_SIGNATURES_PATH'),
        'block_threshold' => env('CYBERSHIELD_SIGNATURE_BLOCK_THRESHOLD', 'medium'), // low, medium, high, critical
    ],

    'monitoring' => [
        'log_channel' => env('CYBERSHIELD_LOG_CHANNEL', 'stack'),
        'db_logging' => true,
        'exclude_paths' => ['/_debugbar*', '/horizon*'],
    ],

    'whitelist' => [
        '127.0.0.1',
    ],

    'blacklist' => [
        // '1.2.3.4',
        // '192.168.1.0/24',
    ],

    'logging' => [
        'enabled' => env('CYBERSHIELD_LOGGING_ENABLED', true),
        
        'channels' => [
            'request'    => true,
            'api'        => true,
            'bot'        => true,
            'threat'     => true,
            'system'     => true,
            'traffic'    => true,
            'database'   => true,
            'queue'      => true,
            'middleware' => true,
        ],

        'format' => env('CYBERSHIELD_LOG_FORMAT', '[{datetime}] {level} {ip} {user_id} {method} {url} {status} {message}'),

        'rotation' => env('CYBERSHIELD_LOG_ROTATION', 'daily'),
        
        'max_size' => env('CYBERSHIELD_LOG_MAX_SIZE', 5242880), // 5MB
    ],
];

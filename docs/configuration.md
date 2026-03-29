# ⚙️ Configuration Reference

Complete reference for `config/cybershield.php` — the central brain of all CyberShield behavior.

> [!NOTE]
> All values can be overridden via `.env`. The config file contains sensible production defaults. Run `php artisan vendor:publish --tag=cybershield-config` to get your own copy.

---

## Top-Level Settings

```php
'enabled' => env('CYBERSHIELD_ENABLED', true),
```
Master switch. Set to `false` to disable CyberShield without removing it.

```php
'global_mode' => env('CYBERSHIELD_GLOBAL_MODE', 'active'),
```
Controls all middleware behavior:
- `'active'` — Threats are blocked with HTTP error responses (production default)
- `'log'` — Threats are logged only, requests still pass through (safe for onboarding/staging)

---

## Module Toggles

Enable or disable entire security modules independently:

```php
'modules' => [
    'request_security'  => env('CYBERSHIELD_REQUEST_SECURITY_ENABLED', true),
    'rate_limiting'     => env('CYBERSHIELD_RATE_LIMITING_ENABLED', true),
    'bot_protection'    => env('CYBERSHIELD_BOT_PROTECTION_ENABLED', true),
    'network_security'  => env('CYBERSHIELD_NETWORK_SECURITY_ENABLED', true),
    'auth_security'     => env('CYBERSHIELD_AUTH_SECURITY_ENABLED', true),
    'api_security'      => env('CYBERSHIELD_API_SECURITY_ENABLED', true),
    'threat_detection'  => env('CYBERSHIELD_THREAT_DETECTION_ENABLED', true),
    'monitoring'        => env('CYBERSHIELD_MONITORING_ENABLED', true),
],
```

---

## Request Security

```php
'request_security' => [
    // Maximum body size in bytes (default 5MB)
    'max_request_size' => env('CYBERSHIELD_MAX_SIZE', 5242880),

    // Force HTTPS on all requests
    'enforce_https' => env('CYBERSHIELD_ENFORCE_HTTPS', true),

    // CORS allowed origins (comma-separated in .env)
    'allowed_origins' => explode(',', env('CYBERSHIELD_ALLOWED_ORIGINS', 'localhost')),

    // Allowed Content-Type headers
    'allowed_content_types' => ['application/json', 'text/html', 'multipart/form-data'],

    // Headers that MUST be present on every request
    'required_headers' => ['User-Agent', 'Accept'],

    // Hostnames allowed to connect to this app
    'trusted_hosts' => ['localhost', '127.0.0.1'],

    // Require X-Requested-With: XMLHttpRequest on all non-GET routes
    'ajax_only' => false,
],
```

---

## Rate Limiting

```php
'rate_limiting' => [
    'enabled' => env('CYBERSHIELD_RATE_LIMITING_ENABLED', true),
    'driver'  => env('CYBERSHIELD_RATE_LIMIT_DRIVER', 'cache'),

    // Global IP-based limit (applies to all routes)
    'ip_limit_details' => [
        'limit'    => 60,           // requests
        'window'   => 60,           // seconds
        'strategy' => 'linear',     // linear | exponential | fibonacci
        'message'  => 'Too many requests. Please slow down.',
    ],

    // Login route protection
    'login' => [
        'limit'    => 5,
        'window'   => 300,          // 5 minutes
        'strategy' => 'fibonacci',  // 1s, 2s, 3s, 5s, 8s delays
        'message'  => 'Too many login attempts. Your access is temporarily restricted.',
    ],

    // Registration route protection
    'registration' => [
        'limit'    => 3,
        'window'   => 3600,         // 1 hour
        'strategy' => 'exponential',
        'message'  => 'Too many registration attempts from this IP.',
    ],

    // API endpoint protection
    'api' => [
        'limit'    => 1000,
        'window'   => 3600,         // 1 hour
        'strategy' => 'linear',
    ],
],
```

### Strategy Behavior Comparison

| Strategy | Violation 1 | Violation 2 | Violation 3 | Violation 5 |
|----------|------------|------------|------------|------------|
| `linear` | Block immediately | Block | Block | Block |
| `exponential` | 1s wait | 2s wait | 4s wait | 16s wait |
| `fibonacci` | 1s wait | 2s wait | 3s wait | 8s wait |

---

## Bot Protection

```php
'bot_protection' => [
    'enabled'              => env('CYBERSHIELD_BOT_PROTECTION_ENABLED', true),
    'block_bots'           => env('CYBERSHIELD_BLOCK_BOTS', false),  // Block ALL bots (including crawlers)
    'block_headless'       => true,   // Block Puppeteer/Playwright
    'block_scrapers'       => true,   // Block scraping tools
    'pacing_limit'         => 50,     // Max requests per pacing window
    'pacing_window'        => 10,     // Pacing window in seconds
    'block_response_code'  => 403,

    // Honeypot field configuration
    'honeypot' => [
        'enabled'    => true,
        'field_name' => 'hp_token_id',  // Hidden field name bots will fill
    ],

    // Known bot User-Agent substrings (case-insensitive partial match)
    'bots' => [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
        'curl', 'python', 'postman', 'selenium', 'headless', 'phantomjs',
        'scrapy', 'wget', 'urllib', 'httpclient', 'php', 'perl', 'ruby'
    ],

    // Headers that indicate automation scripts
    'suspicious_headers' => [
        'X-Puppeteer-Request',
        'X-Selenium-Driver',
        'X-Headless-Chrome'
    ],

    // Headers that real browsers always send (absence = bot signal)
    'browser_common_headers' => [
        'Accept', 'Accept-Encoding', 'Accept-Language'
    ],
],
```

---

## Network Security

```php
'network_security' => [
    // Block all TOR exit node traffic
    'block_tor' => env('CYBERSHIELD_BLOCK_TOR', false),

    // ISO country codes to block globally (e.g., ['CN', 'RU', 'KP'])
    'blocked_countries' => [],

    // Region names to block (from X-Region header)
    'blocked_regions' => [],

    // IP threat score threshold for automatic block (0-100)
    'threat_score_threshold' => 80,

    // Response messages (supports {ip}, {reason}, {country} placeholders)
    'messages' => [
        'blacklisted'  => 'Your IP address ({ip}) is blacklisted.',
        'blocked'      => 'Your IP address ({ip}) has been blocked. Reason: {reason}',
        'geo_blocked'  => 'Access denied from your location ({country}).',
        'tor_blocked'  => 'Access via TOR network is not allowed.',
    ],
],
```

---

## Auth Security

```php
'auth_security' => [
    // Regex for strong password validation
    // Default: min 8 chars, at least 1 lowercase, 1 uppercase, 1 digit
    'strong_password_regex' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',

    // Session inactivity timeout in seconds (1 hour)
    'session_timeout' => 3600,

    // Max failed login attempts before hard block
    'login_attempts_limit' => 5,
],
```

---

## API Security

```php
'api_security' => [
    'enabled'                  => env('CYBERSHIELD_API_SECURITY_ENABLED', true),
    'keys_table'               => 'api_keys',       // DB table for API key registry
    'signature_algo'           => 'sha256',          // HMAC algorithm
    'timestamp_tolerance'      => 60,                // Seconds of clock drift tolerance
    'verify_signature'         => env('CYBERSHIELD_API_VERIFY_SIGNATURE', true),
    'replay_protection'        => env('CYBERSHIELD_API_REPLAY_PROTECTION', true),
    'default_concurrent_limit' => 10,               // Max parallel requests per client
    'daily_cost_limit'         => 10000,            // Total cost budget per client per day
    'abuse_threshold'          => 100,              // Requests per 10s before auto-block
    'auto_block'               => env('CYBERSHIELD_API_AUTO_BLOCK', true),

    // HTTP headers used for API security values
    'headers' => [
        'key'       => 'X-API-KEY',
        'signature' => 'X-Signature',
        'nonce'     => 'X-Nonce',
        'timestamp' => 'X-Timestamp',
    ],

    // Headers used to build client fingerprint
    'fingerprint_headers' => [
        'User-Agent', 'Accept-Language', 'Accept-Encoding',
    ],

    // Resource cost per endpoint (prevents exhaustion attacks)
    // Clients have a daily_cost_limit budget
    'endpoint_costs' => [
        'api/v1/heavy-endpoint' => 50,
        'api/v1/export'         => 20,
        'api/v1/search'         => 5,
        // default cost for unlisted routes = 1
    ],
],
```

---

## WAF / Firewall

```php
'firewall' => [
    // Which request targets to scan
    // Options: 'uri', 'query', 'headers', 'body'
    'inspection_targets' => ['query', 'body', 'headers', 'uri'],

    // IP block duration (in days) per threat severity
    'blocking_ttl' => [
        'low'      => 1,   // 1 day  - Warning
        'medium'   => 3,   // 3 days - Moderate threat
        'high'     => 7,   // 7 days - Serious threat
        'critical' => 30,  // 30 days - Severe threat (manual review recommended)
    ],
],
```

---

## Malware Scanner

```php
'malware_scanner' => [
    // Code patterns considered malicious
    'suspicious_patterns' => [
        'eval\(',         // Dynamic code execution
        'base64_decode\(',// Obfuscation
        'shell_exec\(',   // Shell access
        'system\(',       // System command execution
        'passthru\(',     // Pass-through execution
        'exec\(',
        'popen\(',
        'proc_open\(',
        'pcntl_exec\(',
        'assert\(',
        'preg_replace\(.*?\/e',  // preg_replace with /e modifier (RCE)
        'gzinflate\(',           // Common in packed shells
        'str_rot13\(',           // Obfuscation
    ],

    // File extensions scanned by the malware scanner command
    'scanned_extensions' => ['php', 'phtml', 'php3', 'php4', 'php5', 'phps'],
],
```

---

## Threat Detection

```php
'threat_detection' => [
    'log_threats'      => true,   // Write threat events to security_logs
    'block_on_threat'  => true,   // Block request when threat detected
    'sql_injection'    => true,   // Enable SQLi detection
    'xss_attack'       => true,   // Enable XSS detection
    'rce_attack'       => true,   // Enable RCE detection
    'traversal_attack' => true,   // Enable path traversal detection

    // Points added to IP threat score per event type
    'scoring' => [
        'insecure_request'        => 10,  // Missing HTTPS, bad protocol
        'missing_accept_language' => 20,  // Bot signal
        'suspicious_user_agent'   => 30,  // Known attack tool UA
    ],

    // How long threat scores persist (seconds) — 24 hours
    'score_ttl' => 86400,
],
```

---

## Signatures

```php
'signatures' => [
    // Directory containing built-in JSON signature files
    'path' => env('CYBERSHIELD_SIGNATURES_PATH', base_path('src/Signatures')),

    // Optional: path to your custom JSON signature files
    'custom_path' => env('CYBERSHIELD_CUSTOM_SIGNATURES_PATH'),

    // Minimum severity level to trigger a block
    // 'low'      — Block everything (very aggressive)
    // 'medium'   — Block medium+ (recommended)
    // 'high'     — Block high+ only (conservative)
    // 'critical' — Block critical only (monitoring mode)
    'block_threshold' => env('CYBERSHIELD_SIGNATURE_BLOCK_THRESHOLD', 'medium'),
],
```

---

## Monitoring & Logging

```php
'monitoring' => [
    'log_channel'   => env('CYBERSHIELD_LOG_CHANNEL', 'stack'),
    'db_logging'    => true,
    'exclude_paths' => ['/_debugbar*', '/horizon*'],  // Paths to skip from logging
],

'logging' => [
    'enabled' => env('CYBERSHIELD_LOGGING_ENABLED', true),

    // Enable/disable individual log channels
    'channels' => [
        'request'    => true,  // All HTTP requests
        'api'        => true,  // API calls with key/signature info
        'bot'        => true,  // Bot detection events
        'threat'     => true,  // WAF blocks and threat events
        'system'     => true,  // System-level events (startup, config)
        'traffic'    => true,  // Traffic volume metrics
        'database'   => true,  // DB query security events
        'queue'      => true,  // Background job security events
        'middleware'  => true, // Individual middleware decisions
    ],

    // Log entry format string
    // Available tokens: {datetime}, {level}, {ip}, {user_id}, {method}, {url}, {status}, {message}
    'format' => env('CYBERSHIELD_LOG_FORMAT',
        '[{datetime}] {level} {ip} {user_id} {method} {url} {status} {message}'
    ),

    // How often to rotate log files
    'rotation' => env('CYBERSHIELD_LOG_ROTATION', 'daily'),  // daily | weekly

    // Maximum log file size before rotation (5MB)
    'max_size' => env('CYBERSHIELD_LOG_MAX_SIZE', 5242880),
],
```

---

## IP Whitelist & Blacklist

```php
// IPs/CIDRs that always bypass ALL security checks
'whitelist' => [
    '127.0.0.1',
    // '10.0.0.0/8',       // Office network
    // '203.0.113.5',      // Trusted partner IP
],

// IPs/CIDRs that are permanently blocked
'blacklist' => [
    // '1.2.3.4',
    // '192.168.100.0/24',
],
```

---

## Project Scanner Rules

```php
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
```

You can add your own rule classes to this array. Each class must implement `CyberShield\Security\Project\Contracts\RuleInterface`.

---

[← Back to README](../README.md) | [Next: Firewall →](firewall.md)

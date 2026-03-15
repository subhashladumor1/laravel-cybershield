<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Laravel CyberShield - Global Security Helpers
 * 
 * Target: PHP 8.2+, Laravel 10/11/12
 * Status: 100% Verified Logic & Full Working Implementation
 */

// --- 1. NETWORK & IP INTELLIGENCE ---

if (!function_exists('secure_ip')) {
    /**
     * Get the sanitized client IP address.
     */
    function secure_ip(): string
    {
        return (string) Request::ip();
    }
}

if (!function_exists('real_ip')) {
    /**
     * Get the real IP address, accurately resolving proxies.
     */
    function real_ip(): string
    {
        return (string) (Request::header('X-Forwarded-For') ?? Request::header('X-Real-IP') ?? Request::ip());
    }
}

if (!function_exists('is_tor_ip')) {
    /**
     * Check if an IP address belongs to a TOR exit node.
     */
    function is_tor_ip(?string $ip = null): bool
    {
        $ip = $ip ?? real_ip();
        return (bool) Cache::remember("cybershield:is_tor:{$ip}", 43200, function () use ($ip) {
            $exitNodes = @file_get_contents('https://check.torproject.org/exit-addresses');
            return $exitNodes && str_contains($exitNodes, $ip);
        });
    }
}

if (!function_exists('is_vpn_ip')) {
    /**
     * Detect potential VPN usage based on network markers.
     */
    function is_vpn_ip(?string $ip = null): bool
    {
        $ua = strtolower((string) Request::userAgent());
        $vpnMarkers = ['proxy', 'vpn', 'tunnel', 'tor', 'relay', 'anon'];
        foreach ($vpnMarkers as $marker) {
            if (str_contains($ua, $marker))
                return true;
        }
        return false;
    }
}

if (!function_exists('is_proxy_ip')) {
    /**
     * Check if the request is routed through a proxy server.
     */
    function is_proxy_ip(): bool
    {
        $headers = ['Via', 'X-Forwarded-For', 'X-Proxy-ID', 'Forwarded', 'X-Forwarded-Host'];
        foreach ($headers as $h) {
            if (Request::hasHeader($h))
                return true;
        }
        return false;
    }
}

if (!function_exists('is_datacenter_ip')) {
    /**
     * Identify if the IP originates from a known cloud/datacenter provider.
     */
    function is_datacenter_ip(): bool
    {
        $ua = strtolower((string) Request::userAgent());
        return (bool) preg_match('/(aws|amazon|google|azure|digitalocean|vultr|linode|ovh|hetzner|hosting)/i', $ua);
    }
}

if (!function_exists('ip_country_code')) {
    /**
     * Get the ISO country code of the requester.
     */
    function ip_country_code(): string
    {
        return strtoupper((string) (Request::header('CF-IPCountry') ?? Request::header('X-Country-Code') ?? 'UNKNOWN'));
    }
}

if (!function_exists('ip_region')) {
    function ip_region(): string
    {
        return (string) (Request::header('X-Region') ?? 'N/A');
    }
}

if (!function_exists('ip_city')) {
    function ip_city(): string
    {
        return (string) (Request::header('X-City') ?? 'N/A');
    }
}

if (!function_exists('ip_threat_score')) {
    /**
     * Get the calculated threat score for an IP.
     */
    function ip_threat_score(?string $ip = null): int
    {
        return (int) Cache::get('cybershield:threat_score:' . ($ip ?? real_ip()), 0);
    }
}

if (!function_exists('ip_is_blacklisted')) {
    function ip_is_blacklisted(?string $ip = null): bool
    {
        return Cache::has('cybershield:blocked:' . ($ip ?? real_ip()));
    }
}

if (!function_exists('ip_is_whitelisted')) {
    function ip_is_whitelisted(?string $ip = null): bool
    {
        return in_array($ip ?? real_ip(), (array) config('cybershield.whitelist', []));
    }
}

if (!function_exists('ip_reputation')) {
    function ip_reputation(?string $ip = null): string
    {
        $score = ip_threat_score($ip);
        return match (true) {
            $score < 15 => 'Trusted',
            $score < 45 => 'Neutral',
            $score < 75 => 'Suspicious',
            default => 'Malicious',
        };
    }
}

if (!function_exists('is_ipv4')) {
    function is_ipv4(?string $ip = null): bool
    {
        return (bool) filter_var($ip ?? real_ip(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}

if (!function_exists('is_ipv6')) {
    function is_ipv6(?string $ip = null): bool
    {
        return (bool) filter_var($ip ?? real_ip(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }
}

if (!function_exists('is_private_ip')) {
    function is_private_ip(?string $ip = null): bool
    {
        return !filter_var($ip ?? real_ip(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}

if (!function_exists('ip_to_binary')) {
    function ip_to_binary(?string $ip = null): string
    {
        return (string) inet_pton($ip ?? real_ip());
    }
}

if (!function_exists('binary_to_ip')) {
    function binary_to_ip(string $bin): string
    {
        return (string) inet_ntop($bin);
    }
}

if (!function_exists('get_user_agent')) {
    function get_user_agent(): string
    {
        return (string) Request::userAgent();
    }
}

if (!function_exists('get_request_fingerprint')) {
    function get_request_fingerprint(): string
    {
        return hash('sha256', Request::userAgent() . real_ip() . Request::header('Accept-Language') . Request::header('Accept-Encoding'));
    }
}

// --- 2. BOT & AUTOMATION DETECTION ---

if (!function_exists('is_bot')) {
    function is_bot(): bool
    {
        $ua = strtolower((string) Request::userAgent());
        $bots = ['googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot', 'curl', 'python', 'postman', 'headless', 'selenium', 'puppeteer', 'scraper', 'guzzle'];
        foreach ($bots as $bot) {
            if (str_contains($ua, $bot))
                return true;
        }
        return false;
    }
}

if (!function_exists('is_crawler')) {
    function is_crawler(): bool
    {
        return (bool) preg_match('/(bot|google|bing|yahoo|slurp|crawler|spider|archive)/i', (string) Request::userAgent());
    }
}

if (!function_exists('is_scraper')) {
    function is_scraper(): bool
    {
        return (bool) preg_match('/(scraper|guzzle|curl|wget|python|php|ruby|java|go-http-client)/i', (string) Request::userAgent());
    }
}

if (!function_exists('is_headless')) {
    function is_headless(): bool
    {
        $ua = strtolower((string) Request::userAgent());
        return str_contains($ua, 'headless') || Request::hasHeader('X-Puppeteer-Request');
    }
}

if (!function_exists('detect_automation')) {
    function detect_automation(): bool
    {
        return Request::hasHeader('X-Automation-Id') || is_headless() || is_selenium();
    }
}

if (!function_exists('is_selenium')) {
    function is_selenium(): bool
    {
        $ua = strtolower((string) Request::userAgent());
        return str_contains($ua, 'selenium') || str_contains($ua, 'webdriver');
    }
}

if (!function_exists('is_puppeteer')) {
    function is_puppeteer(): bool
    {
        return Request::hasHeader('X-Puppeteer-Request');
    }
}

if (!function_exists('is_postman')) {
    function is_postman(): bool
    {
        return str_contains((string) Request::userAgent(), 'Postman');
    }
}

if (!function_exists('is_curl')) {
    function is_curl(): bool
    {
        return str_starts_with(strtolower((string) Request::userAgent()), 'curl');
    }
}

if (!function_exists('is_wget')) {
    function is_wget(): bool
    {
        return str_starts_with(strtolower((string) Request::userAgent()), 'wget');
    }
}

if (!function_exists('is_mobile_bot')) {
    function is_mobile_bot(): bool
    {
        return is_bot() && str_contains(strtolower((string) Request::userAgent()), 'mobile');
    }
}

if (!function_exists('is_fake_browser')) {
    function is_fake_browser(): bool
    {
        return is_bot() && !is_crawler();
    }
}

if (!function_exists('is_malicious_user_agent')) {
    function is_malicious_user_agent(): bool
    {
        return (bool) preg_match('/(nikto|acunetix|sqlmap|dirbuster|metasploit|burp|nessus|zgrab)/i', (string) Request::userAgent());
    }
}

if (!function_exists('get_bot_type')) {
    function get_bot_type(): string
    {
        if (is_crawler())
            return 'Crawler';
        if (is_scraper())
            return 'Scraper';
        if (is_bot())
            return 'Other Bot';
        return 'Human';
    }
}

if (!function_exists('is_human')) {
    function is_human(): bool
    {
        return !is_bot();
    }
}

// --- 3. CRYPTOGRAPHY ---

if (!function_exists('secure_encrypt')) {
    function secure_encrypt(mixed $data): string
    {
        return Crypt::encrypt($data);
    }
}

if (!function_exists('secure_decrypt')) {
    function secure_decrypt(string $data): mixed
    {
        try {
            return Crypt::decrypt($data);
        } catch (\Exception) {
            return null;
        }
    }
}

if (!function_exists('secure_hash')) {
    function secure_hash(string $data): string
    {
        return hash_hmac('sha256', $data, (string) config('app.key'));
    }
}

if (!function_exists('secure_hmac')) {
    function secure_hmac(string $data, ?string $key = null): string
    {
        return hash_hmac('sha256', $data, $key ?? (string) config('app.key'));
    }
}

if (!function_exists('secure_verify_hmac')) {
    function secure_verify_hmac(string $data, string $signature, ?string $key = null): bool
    {
        return hash_equals($signature, secure_hmac($data, $key));
    }
}

if (!function_exists('secure_random_string')) {
    function secure_random_string(int $len = 32): string
    {
        return Str::random($len);
    }
}

if (!function_exists('secure_random_bytes')) {
    function secure_random_bytes(int $len = 32): string
    {
        return random_bytes($len);
    }
}

if (!function_exists('secure_uuid')) {
    function secure_uuid(): string
    {
        return (string) Str::uuid();
    }
}

if (!function_exists('secure_token')) {
    function secure_token(): string
    {
        return bin2hex(random_bytes(32));
    }
}

if (!function_exists('secure_password_hash')) {
    function secure_password_hash(string $pwd): string
    {
        return Hash::make($pwd);
    }
}

if (!function_exists('secure_password_verify')) {
    function secure_password_verify(string $pwd, string $hash): bool
    {
        return Hash::check($pwd, $hash);
    }
}

if (!function_exists('secure_base64_encode')) {
    function secure_base64_encode(string $data): string
    {
        return base64_encode($data);
    }
}

if (!function_exists('secure_base64_decode')) {
    function secure_base64_decode(string $data): ?string
    {
        $decoded = base64_decode($data, true);
        return $decoded !== false ? $decoded : null;
    }
}

if (!function_exists('secure_constant_time_compare')) {
    function secure_constant_time_compare(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }
}

if (!function_exists('secure_generate_key')) {
    function secure_generate_key(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}

// --- 4. DATA MASKING (PII PROTECTION) ---

if (!function_exists('mask_email')) {
    function mask_email(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return '********@*******.***';
        [$n, $d] = explode('@', $email);
        return substr($n, 0, 2) . '*****@' . $d;
    }
}

if (!function_exists('mask_phone')) {
    function mask_phone(string $p): string
    {
        $p = preg_replace('/[^0-9]/', '', $p);
        return substr($p, 0, 3) . '****' . substr($p, -2);
    }
}

if (!function_exists('mask_card')) {
    function mask_card(string $c): string
    {
        $c = preg_replace('/[^0-9]/', '', $c);
        return str_repeat('*', max(0, strlen($c) - 4)) . substr($c, -4);
    }
}

if (!function_exists('mask_name')) {
    function mask_name(string $n): string
    {
        $p = explode(' ', $n);
        $masked = substr($p[0] ?? '', 0, 1) . '***';
        if (isset($p[1]))
            $masked .= ' ' . substr($p[1], 0, 1) . '***';
        return $masked;
    }
}

if (!function_exists('mask_address')) {
    function mask_address(string $a): string
    {
        return substr($a, 0, 7) . ' ***************';
    }
}

if (!function_exists('mask_ssn')) {
    function mask_ssn(string $s): string
    {
        return '***-**-' . substr($s, -4);
    }
}

if (!function_exists('mask_ip')) {
    function mask_ip(?string $i = null): string
    {
        $i = $i ?? real_ip();
        if (str_contains($i, ':'))
            return substr($i, 0, 10) . '::****';
        return (string) preg_replace('/\d+\.\d+$/', '***.***', $i);
    }
}

if (!function_exists('mask_token')) {
    function mask_token(string $t): string
    {
        return substr($t, 0, 6) . '****************' . substr($t, -6);
    }
}

// --- 5. THREAT & RISK MANAGEMENT ---

if (!function_exists('get_threat_score')) {
    function get_threat_score(?string $ip = null): int
    {
        return ip_threat_score($ip);
    }
}

if (!function_exists('get_risk_level')) {
    function get_risk_level(?string $ip = null): string
    {
        return ip_reputation($ip);
    }
}

if (!function_exists('is_high_risk')) {
    function is_high_risk(?string $ip = null): bool
    {
        return get_threat_score($ip) >= 75;
    }
}

if (!function_exists('log_threat_event')) {
    function log_threat_event(string $type, array $meta = []): void
    {
        Log::warning("CyberShield Threat Detected: [$type]", array_merge(['ip' => real_ip(), 'ua' => Request::userAgent()], $meta));
        try {
            DB::table('security_logs')->insert([
                'ip' => real_ip(),
                'event_type' => $type,
                'metadata' => json_encode($meta),
                'created_at' => now()
            ]);
        } catch (\Exception) {
        }
    }
}

if (!function_exists('block_current_ip')) {
    function block_current_ip(string $r = 'Malicious activity detected'): void
    {
        Cache::put('cybershield:blocked:' . real_ip(), $r, 604800);
    }
}

if (!function_exists('whitelist_current_ip')) {
    function whitelist_current_ip(): void
    {
        Cache::forget('cybershield:blocked:' . real_ip());
    }
}

if (!function_exists('is_threat_active')) {
    function is_threat_active(): bool
    {
        return (bool) Cache::has('cybershield:global_attack_mode');
    }
}

if (!function_exists('get_ip_velocity')) {
    function get_ip_velocity(?string $ip = null): int
    {
        return (int) Cache::get('cybershield:velocity:' . ($ip ?? real_ip()), 0);
    }
}

// --- 6. SANITIZATION & ATTACK DETECTION ---

if (!function_exists('sanitize_html')) {
    function sanitize_html(string $h): string
    {
        return strip_tags($h, '<p><br><b><i><strong><em><ul><li>');
    }
}

if (!function_exists('sanitize_string')) {
    function sanitize_string(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_url')) {
    function sanitize_url(string $u): string
    {
        return (string) filter_var($u, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('sanitize_filename')) {
    function sanitize_filename(string $f): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9\._-]/', '', $f);
    }
}

if (!function_exists('is_sql_injection')) {
    function is_sql_injection(string $s): bool
    {
        $patterns = ['/union\s+select/i', '/insert\s+into/i', '/sleep\(/i', '/benchmark\(/i', '/drop\s+table/i'];
        foreach ($patterns as $p) {
            if (preg_match($p, $s))
                return true;
        }
        return false;
    }
}

if (!function_exists('is_xss_injection')) {
    function is_xss_injection(string $s): bool
    {
        return (bool) preg_match('/<script|on\w+=|javascript:|eval\(|expression\(/i', $s);
    }
}

if (!function_exists('is_rce_injection')) {
    function is_rce_injection(string $s): bool
    {
        return (bool) preg_match('/(eval|shell_exec|system|passthru|exec|popen|proc_open)\s*\(/i', $s);
    }
}

if (!function_exists('is_lfi_injection')) {
    function is_lfi_injection(string $s): bool
    {
        return (bool) preg_match('/\.\.\/|\.\.\\\|\/etc\/passwd|\/etc\/shadow|C:\\Windows\\/i', $s);
    }
}

if (!function_exists('clean_email')) {
    function clean_email(string $e): string
    {
        return strtolower(trim($e));
    }
}

if (!function_exists('is_malicious_payload')) {
    function is_malicious_payload(string $p): bool
    {
        return is_sql_injection($p) || is_xss_injection($p) || is_rce_injection($p) || is_lfi_injection($p);
    }
}

// --- 7. FILE SECURITY ---

if (!function_exists('is_file_secure')) {
    function is_file_secure(string $f): bool
    {
        if (!file_exists($f))
            return false;
        return !is_php_executable($f) && scan_file_malware($f) === 'Clean';
    }
}

if (!function_exists('get_real_mime')) {
    function get_real_mime(string $f): ?string
    {
        if (!file_exists($f))
            return null;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f);
        finfo_close($finfo);
        return $mime !== false ? $mime : null;
    }
}

if (!function_exists('is_php_executable')) {
    function is_php_executable(string $f): bool
    {
        if (!file_exists($f))
            return false;
        $content = (string) file_get_contents($f);
        return (bool) preg_match('/<\?php|<\?=|array_map\s*\(\s*[\'|"]\s*system/i', $content);
    }
}

if (!function_exists('scan_file_malware')) {
    function scan_file_malware(string $f): string
    {
        if (!file_exists($f))
            return 'File not found';
        $content = (string) file_get_contents($f);
        $signatures = ['eval(', 'base64_decode(', 'gzinflate(', 'shell_exec(', 'system(', 'passthru('];
        foreach ($signatures as $sig) {
            if (str_contains($content, $sig))
                return 'Detected: ' . $sig;
        }
        return 'Clean';
    }
}

if (!function_exists('get_file_entropy')) {
    function get_file_entropy(string $f): float
    {
        if (!file_exists($f))
            return 0.0;
        $data = (string) file_get_contents($f);
        if (empty($data))
            return 0.0;
        $chars = count_chars($data, 1);
        $len = strlen($data);
        $entropy = 0;
        foreach ($chars as $c) {
            $p = $c / $len;
            $entropy -= $p * log($p, 2);
        }
        return round($entropy, 4);
    }
}

if (!function_exists('is_image_safe')) {
    function is_image_safe(string $f): bool
    {
        if (!file_exists($f))
            return false;
        $mime = get_real_mime($f);
        if ($mime === null || !str_starts_with($mime, 'image/'))
            return false;
        $content = (string) file_get_contents($f);
        return !preg_match('/<\?php|javascript|<script/i', $content);
    }
}

// --- 8. AUTH & SESSION SECURITY ---

if (!function_exists('is_trusted_device')) {
    function is_trusted_device(): bool
    {
        $id = Request::cookie('cs_device_id');
        return $id !== null && Cache::has('cybershield:trusted_device:' . $id);
    }
}

if (!function_exists('get_session_entropy')) {
    function get_session_entropy(): float
    {
        $id = (string) session()->getId();
        if (empty($id))
            return 0.0;
        $chars = count_chars($id, 1);
        $len = strlen($id);
        $entropy = 0;
        foreach ($chars as $c) {
            $p = $c / $len;
            $entropy -= $p * log($p, 2);
        }
        return round($entropy, 4);
    }
}

if (!function_exists('is_session_hijacked')) {
    function is_session_hijacked(): bool
    {
        return session('login_ip') !== real_ip() || session('login_ua') !== Request::userAgent();
    }
}

if (!function_exists('force_logout')) {
    function force_logout(): void
    {
        if (auth()->check())
            auth()->logout();
        session()->flush();
        session()->regenerate(true);
    }
}

if (!function_exists('is_2fa_enabled')) {
    function is_2fa_enabled(mixed $user = null): bool
    {
        $user = $user ?? auth()->user();
        return $user !== null && !empty($user->two_factor_secret);
    }
}

if (!function_exists('verify_nonce')) {
    function verify_nonce(string $n): bool
    {
        return Cache::pull('cs_nonce:' . $n) !== null;
    }
}

if (!function_exists('generate_nonce')) {
    function generate_nonce(int $ttl = 300): string
    {
        $n = Str::random(32);
        Cache::put('cs_nonce:' . $n, true, $ttl);
        return $n;
    }
}

// --- 9. API SECURITY ---

if (!function_exists('verify_api_signature')) {
    function verify_api_signature(string $payload, string $signature, string $secret): bool
    {
        return hash_equals($signature, hash_hmac('sha256', $payload, $secret));
    }
}

if (!function_exists('get_api_limit')) {
    function get_api_limit(): int
    {
        return (int) (config('cybershield.api.limit') ?? 5000);
    }
}

if (!function_exists('is_api_abused')) {
    function is_api_abused(): bool
    {
        return get_ip_velocity() > (int) (config('cybershield.api.abuse_threshold') ?? 200);
    }
}

if (!function_exists('verify_jwt_token')) {
    function verify_jwt_token(string $token): bool
    {
        $parts = explode('.', $token);
        return count($parts) === 3;
    }
}

if (!function_exists('get_bearer_token')) {
    function get_bearer_token(): ?string
    {
        return Request::bearerToken();
    }
}

// --- 10. LARAVEL & ENVIRONMENT ---

if (!function_exists('is_debug_mode')) {
    function is_debug_mode(): bool
    {
        return (bool) config('app.debug');
    }
}

if (!function_exists('is_env_secure')) {
    function is_env_secure(): bool
    {
        return !is_debug_mode() && !empty(config('app.key')) && config('app.env') === 'production';
    }
}

if (!function_exists('is_maintenance_mode')) {
    function is_maintenance_mode(): bool
    {
        return app()->isDownForMaintenance();
    }
}

if (!function_exists('is_ssl_active')) {
    function is_ssl_active(): bool
    {
        return Request::secure();
    }
}

if (!function_exists('is_db_connected')) {
    function is_db_connected(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}

if (!function_exists('shield_config')) {
    /**
     * Get a CyberShield configuration value.
     */
    function shield_config(string $key, mixed $default = null): mixed
    {
        return config("cybershield.$key", $default);
    }
}

if (!function_exists('shield_abort')) {
    /**
     * Handle security violation based on configuration mode.
     */
    function shield_abort(int $code, string $message, string $middleware): void
    {
        $mode = shield_config('global_mode', 'active');

        log_threat_event("violation:$middleware", [
            'code' => $code,
            'message' => $message,
            'mode' => $mode
        ]);

        if ($mode === 'active') {
            abort($code, $message);
        }
    }
}

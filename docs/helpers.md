# 🦾 Global Security Helper Functions

CyberShield auto-loads `src/Helpers/security_helpers.php` globally via Composer — no imports needed. All 60+ functions are available anywhere in your application.

---

## 📋 Quick Reference Index

| Category | Functions |
|----------|-----------|
| [1. Network & IP Intelligence](#1-network--ip-intelligence) | `secure_ip`, `real_ip`, `is_tor_ip`, `is_vpn_ip`, `is_proxy_ip`, `is_datacenter_ip`, `ip_country_code`, `ip_region`, `ip_city`, `ip_threat_score`, `ip_is_blacklisted`, `ip_is_whitelisted`, `check_ip_range`, `ip_reputation`, `is_ipv4`, `is_ipv6`, `is_private_ip`, `ip_to_binary`, `binary_to_ip` |
| [2. Bot & Automation Detection](#2-bot--automation-detection) | `is_bot`, `is_crawler`, `is_scraper`, `is_headless`, `detect_automation`, `is_selenium`, `is_puppeteer`, `is_postman`, `is_curl`, `is_wget`, `is_mobile_bot`, `is_fake_browser`, `is_malicious_user_agent`, `get_bot_type`, `is_human` |
| [3. Cryptography](#3-cryptography) | `secure_encrypt`, `secure_decrypt`, `secure_hash`, `secure_hmac`, `secure_verify_hmac`, `secure_random_string`, `secure_random_bytes`, `secure_uuid`, `secure_token`, `secure_password_hash`, `secure_password_verify`, `secure_base64_encode`, `secure_base64_decode`, `secure_constant_time_compare`, `secure_generate_key` |
| [4. Data Masking (PII)](#4-data-masking-pii) | `mask_email`, `mask_phone`, `mask_card`, `mask_name`, `mask_address`, `mask_ssn`, `mask_ip`, `mask_token` |
| [5. Threat & Risk Management](#5-threat--risk-management) | `get_threat_score`, `get_risk_level`, `is_high_risk`, `log_threat_event`, `block_current_ip`, `whitelist_current_ip`, `is_threat_active`, `get_ip_velocity` |
| [6. Sanitization & Attack Detection](#6-sanitization--attack-detection) | `sanitize_html`, `sanitize_string`, `sanitize_url`, `sanitize_filename`, `is_sql_injection`, `is_xss_injection`, `is_rce_injection`, `is_lfi_injection`, `clean_email`, `is_malicious_payload` |
| [7. File Security](#7-file-security) | `is_file_secure`, `get_real_mime`, `is_php_executable`, `scan_file_malware`, `get_file_entropy`, `is_image_safe` |
| [8. Auth & Session Security](#8-auth--session-security) | `is_trusted_device`, `get_session_entropy`, `is_session_hijacked`, `force_logout`, `is_2fa_enabled`, `verify_nonce`, `generate_nonce` |
| [9. API Security](#9-api-security) | `verify_api_signature`, `get_api_limit`, `is_api_abused`, `verify_jwt_token`, `get_bearer_token` |
| [10. Laravel & Environment](#10-laravel--environment) | `is_debug_mode`, `is_env_secure`, `is_maintenance_mode`, `is_ssl_active`, `is_db_connected`, `shield_config`, `shield_abort` |

---

## 1. Network & IP Intelligence

### `secure_ip(): string`
Returns the sanitized IP from Laravel's request resolver.
```php
$ip = secure_ip();
// Returns: "203.0.113.45"
```

### `real_ip(): string`
Resolves the true client IP by checking proxy headers (`X-Forwarded-For`, `X-Real-IP`) first. Use this instead of `request()->ip()` in environments behind Cloudflare, AWS ALB, or Nginx proxies.
```php
$ip = real_ip();
// Behind Cloudflare: resolves actual visitor IP from X-Forwarded-For
// Returns: "198.51.100.23"
```

> [!TIP]
> Always use `real_ip()` for forensic logging to ensure your logs are accurate even behind load balancers.

### `is_tor_ip(?string $ip = null): bool`
Checks if an IP is a TOR exit node. Fetches from `check.torproject.org` and caches for 12 hours.
```php
if (is_tor_ip()) {
    abort(403, 'TOR access is not allowed.');
}

// Or check a specific IP
if (is_tor_ip('185.220.101.1')) {
    Log::warning('TOR node attempted access.');
}
```

### `is_vpn_ip(?string $ip = null): bool`
Detects VPN/tunnel markers in the user agent string.
```php
if (is_vpn_ip()) {
    // Additional verification required for VPN users
    return redirect()->route('verify.identity');
}
```

### `is_proxy_ip(): bool`
Checks for common proxy headers (`Via`, `X-Forwarded-For`, `X-Proxy-ID`, `Forwarded`).
```php
if (is_proxy_ip()) {
    log_threat_event('proxy_detected', ['headers' => request()->headers->all()]);
}
```

### `is_datacenter_ip(): bool`
Identifies requests from known cloud/datacenter providers (AWS, Azure, GCP, DigitalOcean).
```php
// Block non-human datacenter traffic on public registration routes
if (is_datacenter_ip() && !auth()->check()) {
    abort(403, 'Automated registrations are not allowed.');
}
```

### `ip_country_code(): string`
Returns the ISO country code from Cloudflare's `CF-IPCountry` or `X-Country-Code` header.
```php
$country = ip_country_code();
// Returns: "US", "IN", "GB", or "UNKNOWN"

if ($country === 'CN' || $country === 'RU') {
    abort(403, 'Access restricted from your region.');
}
```

### `ip_region(): string` / `ip_city(): string`
Returns geographic region/city from CDN headers.
```php
$region = ip_region(); // "California" or "N/A"
$city   = ip_city();   // "San Francisco" or "N/A"
```

### `ip_threat_score(?string $ip = null): int`
Returns the current threat score (0-100) stored in cache for the IP.
```php
$score = ip_threat_score();
// Returns: 0 (clean) to 100 (blocked)

if (ip_threat_score() > 60) {
    // Require additional verification
}
```

### `ip_is_blacklisted(?string $ip = null): bool`
Checks if the IP is currently in the cache-based blacklist.
```php
if (ip_is_blacklisted()) {
    abort(403, 'Your IP has been blocked.');
}
```

### `ip_is_whitelisted(?string $ip = null): bool`
Checks if the IP matches any CIDR ranges in `config('cybershield.whitelist')`.
```php
if (ip_is_whitelisted()) {
    // Skip further checks for trusted office IPs
    return $next($request);
}
```

### `check_ip_range(string $ip, string|array $range): bool`
Checks if an IP matches a CIDR range or exact match. Supports arrays of ranges.
```php
// Single CIDR check
$inRange = check_ip_range('192.168.1.100', '192.168.1.0/24');
// Returns: true

// Array of ranges
$trusted = check_ip_range(real_ip(), ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16']);
// Returns: true if IP is in any private range
```

### `ip_reputation(?string $ip = null): string`
Converts a threat score into a human-readable reputation label.

| Score Range | Label |
|-------------|-------|
| 0 – 14 | `Trusted` |
| 15 – 44 | `Neutral` |
| 45 – 74 | `Suspicious` |
| 75+ | `Malicious` |

```php
$rep = ip_reputation();
if ($rep === 'Malicious') {
    block_current_ip('Reputation threshold exceeded');
}
```

### `is_ipv4(?string $ip = null): bool` / `is_ipv6(?string $ip = null): bool`
```php
is_ipv4('203.0.113.1');  // true
is_ipv6('::1');           // true
```

### `is_private_ip(?string $ip = null): bool`
Returns true if the IP is in a private RFC1918 range.
```php
if (is_private_ip()) {
    // Internal/office traffic — apply relaxed policies
}
```

### `get_user_agent(): string`
```php
$ua = get_user_agent();
// Returns: "Mozilla/5.0 (Windows NT 10.0; Win64; x64)..."
```

### `get_request_fingerprint(): string`
Creates a SHA-256 fingerprint from User-Agent + IP + Accept-Language + Accept-Encoding.
```php
$fp = get_request_fingerprint();
// Useful for detecting session/fingerprint drift
```

---

## 2. Bot & Automation Detection

### `is_bot(): bool`
Checks user agent against a built-in list of known bots and automation tools.
```php
if (is_bot()) {
    // Don't count bots in analytics
    return;
}
```
**Detects**: `googlebot`, `bingbot`, `curl`, `python`, `postman`, `headless`, `selenium`, `puppeteer`, `scraper`, `guzzle`, and more.

### `is_crawler(): bool`
Specifically targets search engine spiders.
```php
if (is_crawler()) {
    // Allow crawlers but serve a minimal/cached response
    return response()->view('seo.minimal');
}
```

### `is_scraper(): bool`
Detects common data harvesting libraries.
```php
if (is_scraper()) {
    abort(403, 'Automated harvesting is not permitted.');
}
```
**Detects**: `guzzle`, `curl`, `wget`, `python`, `php`, `ruby`, `java`, `go-http-client`.

### `is_headless(): bool`
Detects headless Chrome/Firefox browsers (Puppeteer, Playwright).
```php
if (is_headless()) {
    log_threat_event('headless_browser', ['url' => request()->url()]);
    abort(403);
}
```

### `detect_automation(): bool`
Combined check for automation signals: headless + Selenium + `X-Automation-Id` header.
```php
if (detect_automation()) {
    abort(403, 'Automated access is not allowed.');
}
```

### `is_selenium(): bool`
Checks for WebDriver markers in the User-Agent.

### `is_puppeteer(): bool`
Checks for `X-Puppeteer-Request` header.

### `is_postman(): bool`
```php
// Allow Postman only in non-production
if (is_postman() && app()->environment('production')) {
    abort(403, 'API clients must use proper headers.');
}
```

### `is_curl(): bool` / `is_wget(): bool`
Detect CLI download tools.

### `is_malicious_user_agent(): bool`
Detects known attack/pentest tool user agents.
```php
if (is_malicious_user_agent()) {
    block_current_ip('Attack tool detected');
    abort(403);
}
```
**Detects**: `nikto`, `acunetix`, `sqlmap`, `dirbuster`, `metasploit`, `burp`, `nessus`, `zgrab`.

### `get_bot_type(): string`
Returns a friendly bot type: `"Crawler"`, `"Scraper"`, `"Other Bot"`, or `"Human"`.
```php
Log::info('Visitor type: ' . get_bot_type());
```

### `is_human(): bool`
Inverse of `is_bot()`.
```php
if (is_human()) {
    // Track in analytics
    Analytics::track(real_ip());
}
```

---

## 3. Cryptography

### `secure_encrypt(mixed $data): string`
Laravel's Crypt facade with added integrity. Use for encrypting sensitive values before DB storage.
```php
$encrypted = secure_encrypt($user->ssn);
// Store $encrypted in database
```

### `secure_decrypt(string $data): mixed`
Decrypts safely; returns `null` on failure (no exceptions).
```php
$ssn = secure_decrypt($user->encrypted_ssn);
if ($ssn === null) {
    abort(500, 'Data integrity failure.');
}
```

### `secure_hash(string $data): string`
HMAC-SHA256 using `app.key`. Consistent hashing (same input = same output).
```php
$hash = secure_hash($userId . $email);
// Use for verification, not storage (deterministic)
```

### `secure_hmac(string $data, ?string $key = null): string`
SHA-256 HMAC. Uses `app.key` if no key provided.
```php
$signature = secure_hmac($requestBody, $apiSecret);
```

### `secure_verify_hmac(string $data, string $signature, ?string $key = null): bool`
Timing-attack-safe HMAC verification.
```php
$isValid = secure_verify_hmac($requestBody, $providedSignature, $apiSecret);
if (!$isValid) {
    abort(401, 'Invalid signature.');
}
```

### `secure_random_string(int $len = 32): string`
Cryptographically random alphanumeric string.
```php
$token = secure_random_string(64);
// Use for API keys, password reset tokens, etc.
```

### `secure_random_bytes(int $len = 32): string`
Raw random bytes from `random_bytes()`.

### `secure_uuid(): string`
UUID v4 via Str::uuid().
```php
$requestId = secure_uuid();
// "550e8400-e29b-41d4-a716-446655440000"
```

### `secure_token(): string`
64-character hex token from `bin2hex(random_bytes(32))`. Ideal for API tokens.
```php
$apiToken = secure_token();
// "a3b2c1d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2"
```

### `secure_password_hash(string $pwd): string`
Bcrypt password hashing via Laravel's `Hash::make()`.
```php
$hashed = secure_password_hash($request->password);
User::create(['password' => $hashed]);
```

### `secure_password_verify(string $pwd, string $hash): bool`
```php
if (!secure_password_verify($request->password, $user->password)) {
    abort(401, 'Invalid credentials.');
}
```

### `secure_base64_encode(string $data): string` / `secure_base64_decode(string $data): ?string`
```php
$encoded = secure_base64_encode($binaryData);
$decoded = secure_base64_decode($encoded); // null on failure
```

### `secure_constant_time_compare(string $a, string $b): bool`
Timing-attack-safe string comparison using `hash_equals()`.
```php
if (!secure_constant_time_compare($providedToken, $expectedToken)) {
    abort(403);
}
```

### `secure_generate_key(): string`
Generates a new Laravel-compatible app key.
```php
$newKey = secure_generate_key();
// "base64:7Xo8..."
```

---

## 4. Data Masking (PII)

### `mask_email(string $email): string`
```php
echo mask_email('john.doe@gmail.com');
// Output: "jo*****@gmail.com"
```

### `mask_phone(string $phone): string`
```php
echo mask_phone('+1-555-867-5309');
// Output: "155****09"
```

### `mask_card(string $card): string`
```php
echo mask_card('4532 1234 5678 9012');
// Output: "************9012"
```

### `mask_name(string $name): string`
```php
echo mask_name('John Doe');
// Output: "J*** D***"
```

### `mask_address(string $address): string`
```php
echo mask_address('123 Main Street, Springfield, IL 62701');
// Output: "123 Mai ***************"
```

### `mask_ssn(string $ssn): string`
```php
echo mask_ssn('123-45-6789');
// Output: "***-**-6789"
```

### `mask_ip(?string $ip = null): string`
```php
echo mask_ip('203.0.113.45');
// Output: "203.0.***.***"

echo mask_ip('2001:db8::1234');
// Output: "2001:db8::****"
```

### `mask_token(string $token): string`
```php
echo mask_token('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6Ikp...');
// Output: "Bearer****************...Ikp..."
```

---

## 5. Threat & Risk Management

### `get_threat_score(?string $ip = null): int`
Alias for `ip_threat_score()`. Returns 0-100.
```php
$score = get_threat_score();
```

### `get_risk_level(?string $ip = null): string`
Alias for `ip_reputation()`. Returns `Trusted`, `Neutral`, `Suspicious`, or `Malicious`.

### `is_high_risk(?string $ip = null): bool`
Returns `true` if the threat score is ≥ 75.
```php
if (is_high_risk()) {
    // Require step-up authentication
    return redirect()->route('auth.challenge');
}
```

### `log_threat_event(string $type, array $meta = []): void`
Writes to Laravel's log and inserts a record into the `security_logs` table.
```php
log_threat_event('brute_force_attempt', [
    'username' => $request->email,
    'attempt'  => 7,
]);
```

### `block_current_ip(string $reason = 'Malicious activity detected'): void`
Blacklists the current IP in cache for 7 days (604800 seconds).
```php
if ($failedAttempts > 10) {
    block_current_ip('Excessive failed login attempts');
}
```

### `whitelist_current_ip(): void`
Removes the current IP from the blacklist cache.
```php
// After admin review
whitelist_current_ip();
```

### `is_threat_active(): bool`
Checks if a global attack flag is set in cache (`cybershield:global_attack_mode`).
```php
if (is_threat_active()) {
    // Enable additional rate limiting
}
```

### `get_ip_velocity(?string $ip = null): int`
Returns the current request velocity (requests per window) for the IP.
```php
$velocity = get_ip_velocity();
if ($velocity > 100) {
    log_threat_event('high_velocity', ['requests_per_window' => $velocity]);
}
```

---

## 6. Sanitization & Attack Detection

### `sanitize_html(string $html): string`
Strip all tags except safe ones: `<p>`, `<br>`, `<b>`, `<i>`, `<strong>`, `<em>`, `<ul>`, `<li>`.
```php
$safeContent = sanitize_html($request->input('bio'));
```

### `sanitize_string(string $str): string`
HTML-encodes all special characters using `htmlspecialchars()`.
```php
$safe = sanitize_string($userInput);
// "<script>" becomes "&lt;script&gt;"
```

### `sanitize_url(string $url): string`
Removes dangerous characters from URLs.
```php
$cleanUrl = sanitize_url($request->input('redirect'));
```

### `sanitize_filename(string $filename): string`
Strips everything except alphanumeric, `.`, `_`, `-`.
```php
$safeFilename = sanitize_filename($uploadedFile->getClientOriginalName());
// "my file (copy!).php" becomes "myfilecopy.php"
```

### `is_sql_injection(string $input): bool`
Detects common SQL injection patterns.
```php
if (is_sql_injection($request->input('search'))) {
    log_threat_event('sql_injection_attempt');
    abort(422, 'Invalid search query.');
}
```
**Detects**: `UNION SELECT`, `INSERT INTO`, `SLEEP()`, `BENCHMARK()`, `DROP TABLE`.

### `is_xss_injection(string $input): bool`
Detects XSS patterns.
```php
if (is_xss_injection($comment)) {
    abort(422, 'Invalid content detected.');
}
```
**Detects**: `<script>`, `on*=`, `javascript:`, `eval()`, `expression()`.

### `is_rce_injection(string $input): bool`
Detects PHP remote code execution patterns.
```php
if (is_rce_injection($code)) {
    block_current_ip('RCE attempt detected');
    abort(403);
}
```
**Detects**: `eval(`, `shell_exec(`, `system(`, `passthru(`, `exec(`, `popen(`, `proc_open(`.

### `is_lfi_injection(string $input): bool`
Detects Local File Inclusion / Path Traversal.
```php
if (is_lfi_injection($request->input('file'))) {
    abort(403, 'Path traversal detected.');
}
```
**Detects**: `../`, `..\`, `/etc/passwd`, `/etc/shadow`, `C:\Windows\`.

### `is_malicious_payload(string $payload): bool`
Master check — runs SQLi, XSS, RCE, and LFI checks together.
```php
// Perfect for validating any user-provided string
if (is_malicious_payload($request->getContent())) {
    abort(422, 'Malicious payload detected.');
}
```

### `clean_email(string $email): string`
Lowercase + trim for email normalization.
```php
$email = clean_email($request->email);
// "  JOHN@Gmail.COM  " becomes "john@gmail.com"
```

---

## 7. File Security

### `is_file_secure(string $path): bool`
Returns true only if the file exists, is not PHP-executable, and passes malware scan.
```php
$path = storage_path('uploads/' . $filename);
if (!is_file_secure($path)) {
    unlink($path);
    abort(422, 'Uploaded file failed security check.');
}
```

### `get_real_mime(string $path): ?string`
Gets the MIME type using PHP's `finfo` (reads magic bytes, not extension).
```php
$mime = get_real_mime($path);
// Returns: "image/jpeg" — even if attacker renamed file to ".jpg.php"
```

### `is_php_executable(string $path): bool`
Checks file contents for PHP opening tags and execution patterns.
```php
if (is_php_executable($uploadPath)) {
    unlink($uploadPath);
    abort(422, 'Executable files are not allowed.');
}
```

### `scan_file_malware(string $path): string`
Scans file contents for malware signatures.
```php
$result = scan_file_malware('/var/www/uploads/file.php');
// Returns "Clean" or "Detected: eval("
if ($result !== 'Clean') {
    Log::critical('Malware detected in upload', ['result' => $result]);
}
```
**Scans for**: `eval(`, `base64_decode(`, `gzinflate(`, `shell_exec(`, `system(`, `passthru(`.

### `get_file_entropy(string $path): float`
Calculates Shannon entropy of file contents (high entropy = likely obfuscated/encrypted malware).
```php
$entropy = get_file_entropy('/var/www/uploads/file.php');
if ($entropy > 5.5) {
    // Highly obfuscated — flag for manual review
    Log::warning('High entropy file detected', ['entropy' => $entropy, 'file' => $path]);
}
```

### `is_image_safe(string $path): bool`
Checks that file is actually an image (magic bytes) and contains no embedded PHP or JavaScript.
```php
if (!is_image_safe($uploadPath)) {
    unlink($uploadPath);
    abort(422, 'Upload contains unsafe content.');
}
```

---

## 8. Auth & Session Security

### `is_trusted_device(): bool`
Checks if the `cs_device_id` cookie matches a trusted device in cache.
```php
if (!is_trusted_device()) {
    // Require 2FA or email verification for new devices
    return redirect()->route('auth.device-verify');
}
```

### `get_session_entropy(): float`
Calculates the entropy of the current session ID. Low entropy indicates a weak/predictable session.
```php
$entropy = get_session_entropy();
if ($entropy < 3.0) {
    // Regenerate suspicious session
    session()->regenerate(true);
}
```

### `is_session_hijacked(): bool`
Detects session hijacking by comparing current IP and User-Agent to session-stored values.
```php
if (is_session_hijacked()) {
    force_logout();
    log_threat_event('session_hijack_attempt');
    redirect()->route('login');
}
```

### `force_logout(): void`
Immediately logs out the user and flushes + regenerates the session.
```php
// Trigger from admin panel or automated system
force_logout();
```

### `is_2fa_enabled(mixed $user = null): bool`
Checks if `two_factor_secret` is set on the user model.
```php
if (!is_2fa_enabled()) {
    return redirect()->route('2fa.setup')
        ->with('warning', 'Enable 2FA to access this area.');
}
```

### `generate_nonce(int $ttl = 300): string`
Generates a secure one-time token stored in cache.
```php
$nonce = generate_nonce(600); // Expires in 10 minutes
// Include in form: <input type="hidden" name="_nonce" value="{{ $nonce }}">
```

### `verify_nonce(string $nonce): bool`
Validates and immediately consumes a nonce (prevents replay).
```php
if (!verify_nonce($request->input('_nonce'))) {
    abort(419, 'Nonce expired or already used.');
}
```

---

## 9. API Security

### `verify_api_signature(string $payload, string $signature, string $secret): bool`
HMAC-SHA256 signature verification. Timing-safe.
```php
$signature = $request->header('X-Signature');
$payload   = $request->getContent();
$secret    = config('services.my_api.secret');

if (!verify_api_signature($payload, $signature, $secret)) {
    abort(401, 'Invalid API signature.');
}
```

### `get_api_limit(): int`
Returns the configured API limit from config.
```php
$limit = get_api_limit(); // Returns configured limit (default 5000)
```

### `is_api_abused(): bool`
Returns true if the current IP's velocity exceeds the configured `api.abuse_threshold`.
```php
if (is_api_abused()) {
    block_current_ip('API abuse threshold exceeded');
    abort(429);
}
```

### `verify_jwt_token(string $token): bool`
Basic structural validation (checks for 3 segments).
```php
$token = get_bearer_token();
if (!verify_jwt_token($token)) {
    abort(401, 'Invalid token format.');
}
```

### `get_bearer_token(): ?string`
Extracts the Bearer token from the `Authorization` header.
```php
$token = get_bearer_token();
// Returns "eyJhbGc..." or null
```

---

## 10. Laravel & Environment

### `is_debug_mode(): bool`
```php
if (is_debug_mode()) {
    // Show detailed error page
}
```

### `is_env_secure(): bool`
Returns true only if: debug is off, `app.key` is set, and `app.env` is `production`.
```php
if (!is_env_secure()) {
    Log::critical('Application is not in a secure state!');
}
```

### `is_maintenance_mode(): bool`
```php
if (is_maintenance_mode()) {
    // Show custom maintenance UI
}
```

### `is_ssl_active(): bool`
```php
if (!is_ssl_active()) {
    return redirect()->secure(request()->getRequestUri());
}
```

### `is_db_connected(): bool`
```php
if (!is_db_connected()) {
    Log::alert('Database connection failed!');
    abort(503);
}
```

### `shield_config(string $key, mixed $default = null): mixed`
Read any CyberShield config value.
```php
$mode = shield_config('global_mode', 'active');
$threshold = shield_config('signatures.block_threshold', 'medium');
```

### `shield_abort(int $code, string $message, string $middleware): void`
The standardized threat response handler. In `active` mode: sends HTTP error. In `log` mode: logs only and continues.
```php
// Used internally by all CyberShield middlewares
shield_abort(403, 'SQL injection detected.', 'detect_sql_injection');
```

[← Back to README](../README.md) | [Next: Middleware Catalog →](middleware.md)

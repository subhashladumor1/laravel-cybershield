# 🛠️ Global Security Helpers Reference

Laravel CyberShield provides a comprehensive suite of **100+ globally accessible helper functions**. These are strictly typed for **PHP 8.2+** and optimized for **Laravel 10, 11, and 12**.

---

## 📂 1. Network & IP Intelligence
Identify the origin and infrastructure profile of every request.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`secure_ip(): string`** | Returns sanitized IP using Laravel's `Request::ip()`. | `$ip = secure_ip();` |
| **`real_ip(): string`** | Resolves real IP behind proxies (Cloudflare, Nginx) using `X-Forwarded-For`. | `$client = real_ip();` |
| **`is_tor_ip(ip): bool`** | Matches IP against a cached list of TOR exit nodes (refreshed every 12h). | `if(is_tor_ip()) abort(403);` |
| **`is_vpn_ip(ip): bool`** | Scans User-Agent for VPN/Proxy markers like 'tunnel', 'relay', or 'anon'. | `if(is_vpn_ip()) alert('VPN Detected');` |
| **`is_proxy_ip(): bool`** | Checks for standard proxy headers (`Via`, `X-Proxy-ID`, etc.). | `$hidden = is_proxy_ip();` |
| **`is_datacenter_ip(): bool`** | Regex check against known hosting providers (AWS, Azure, DigitalOcean). | `if(is_datacenter_ip()) ...` |
| **`ip_country_code(): string`**| Fetches ISO code from Geo-headers (CF-IPCountry/X-Country-Code). | `$country = ip_country_code();` |
| **`ip_threat_score(ip): int`**| Retrieves historical IP risk score from the local security cache. | `$score = ip_threat_score();` |
| **`ip_reputation(ip): string`**| Categorizes IP as `Trusted`, `Neutral`, `Suspicious`, or `Malicious`. | `$status = ip_reputation();` |
| **`is_private_ip(ip): bool`** | Validates if the IP is within RFC 1918 private ranges. | `if(!is_private_ip()) ...` |
| **`get_request_fingerprint()`**| Generates a SHA-256 hash of UA, IP, and Language headers. | `$id = get_request_fingerprint();` |

---

## 🤖 2. Bot & Automation Detection
Detect crawlers, scrapers, and headless browser environments.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`is_bot(): bool`** | Global check against a library of 15+ known bot/script markers. | `if(is_bot()) rate_limit(10);` |
| **`is_crawler(): bool`** | Specifically identifies search engine spiders (Google, Bing, Yahoo). | `if(is_crawler()) skip_analytics();` |
| **`is_scraper(): bool`** | Identifies scraping libraries like Guzzle, Curl, and Python-Requests. | `if(is_scraper()) show_captcha();` |
| **`is_headless(): bool`** | Detects headless Chrome/Firefox via headers and UA signatures. | `if(is_headless()) log_threat();` |
| **`is_selenium(): bool`** | Finds WebDriver/Selenium automation signatures in the request. | `$is_auto = is_selenium();` |
| **`is_postman(): bool`** | Detects API development tools like Postman or Insomnia. | `if(!is_postman()) ...` |
| **`is_malicious_ua(): bool`** | Scans UA for attack tools (Sqlmap, Nikto, Burp Suite, Zap). | `if(is_malicious_ua()) block();` |
| **`get_bot_type(): string`**| Returns `Crawler`, `Scraper`, `Other Bot`, or `Human`. | `$type = get_bot_type();` |

---

## 🔐 3. Enterprise Cryptography
Security-first wrappers for Laravel's encryption and hashing engine.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`secure_encrypt(data)`** | Authenticated AES-256-GCM encryption with integrity checks. | `$secret = secure_encrypt($val);` |
| **`secure_decrypt(str)`** | Decrypts data; returns `null` if verification fails (no exception). | `$val = secure_decrypt($secret);` |
| **`secure_hmac(data, key)`**| Generates a SHA-256 keyed-hash for data integrity. | `$sig = secure_hmac($payload);` |
| **`secure_verify_hmac()`** | Constant-time comparison to prevent timing attacks. | `secure_verify_hmac($p, $s);` |
| **`secure_password_hash()`** | Uses Argon2id (if supported) or Bcrypt with high-cost factors. | `$hp = secure_password_hash('123');`|
| **`secure_uuid(): string`** | Generates a cryptographically secure UUID v4. | `$id = secure_uuid();` |
| **`secure_token(): string`** | Generates a high-entropy 64-character hex security token. | `$token = secure_token();` |
| **`secure_generate_key()`** | Generates a new Base64 Laravel Application Key. | `$key = secure_generate_key();` |

---

## 🎭 4. Data Masking (PII Protection)
Sanitize sensitive information instantly for logs, views, and reports.

| Function | Logic | Example Output |
| :--- | :--- | :--- |
| **`mask_email(email)`** | Retains domain and first 2 chars of the inbox. | `jo*****@email.com` |
| **`mask_phone(phone)`** | Keeps first 3 and last 2 digits. | `+1 (555) ***-**89` |
| **`mask_card(card)`** | PCI-DSS compliant masking; shows only last 4 strings. | `**** **** **** 4242` |
| **`mask_name(name)`** | Initial-based masking for first and last names. | `J*** D***` |
| **`mask_ssn(ssn)`** | Masks all but the last 4 digits for federal IDs. | `***-**-6789` |
| **`mask_ip(ip)`** | IPv4/IPv6 masking for GDPR compliant tracking. | `192.168.***.***` |
| **`mask_token(token)`** | Shows only the start and end of high-entropy keys. | `sk_liv...89ab2` |

---

## ⚠️ 5. Threat & Risk Management
Real-time response and behavioral tracking utilities.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`is_high_risk(): bool`** | Returns `true` if visitor's threat score exceeds 75. | `if(is_high_risk()) mfa();` |
| **`log_threat_event(typ)`**| Logs to `security_logs` table and Laravel system log. | `log_threat_event('sql_inj');` |
| **`block_current_ip()`** | Immediately bans the IP for 7 days via the Cache driver. | `block_current_ip('Abuse');` |
| **`get_ip_velocity(): int`**| Checks request count for current IP in the last 60s. | `$v = get_ip_velocity();` |
| **`is_threat_active()`** | Checks if the system is in 'Global Attack Mode'. | `if(is_threat_active()) ...` |

---

## 🧹 6. Sanitization & Attack Detection
Cleanse inputs and detect malicious payload patterns.

| Function | Payload Check | Target Attacks |
| :--- | :--- | :--- |
| **`sanitize_html(h)`** | Strips dangerous tags; allows basic b/i/p/ul. | XSS Injection |
| **`sanitize_string(s)`** | Strict HTML entity conversion (UTF-8). | Injection / Broken HTML |
| **`is_sql_injection(s)`** | Scans for `union`, `insert`, `benchmark`, `sleep`. | SQLi |
| **`is_xss_injection(s)`** | Scans for `script`, `onxxx`, `javascript:`, `eval`. | XSS |
| **`is_rce_injection(s)`** | Scans for `shell_exec`, `passthru`, `system`. | Remote Code Execution |
| **`is_lfi_injection(s)`** | Scans for `../`, `/etc/passwd`, `C:\Windows\`. | File Inclusion |
| **`is_malicious_payload()`**| Aggregated check for all major injection types. | Multi-Attack |

---

## 📄 7. File Security
Deep inspection for uploaded files and system resources.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`is_file_secure(f)`** | Combined check: Non-executable + Clean scan. | `if(!is_file_secure($upl)) ...`|
| **`get_real_mime(f)`** | Uses `finfo` to get actual MIME type, bypassing extension. | `$mime = get_real_mime($file);`|
| **`is_php_executable(f)`**| Scans file content for `<?php` or malicious system maps. | `if(is_php_executable($f)) ...`|
| **`scan_file_malware(f)`**| Signature-based scan for shells and obfuscated backdoors. | `$res = scan_file_malware($f);`|
| **`get_file_entropy(f)`** | Calculates **Shannon Entropy** to detect encrypted malware. | `if(get_file_entropy($f) > 7.5)`|
| **`is_image_safe(f)`** | Verifies image MIME + ensures no embedded PHP/JS. | `is_image_safe($profile_pic);`|

---

## 🔑 8. Auth & Session Security
Hardening the user authentication and session lifecycle.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`is_session_hijacked()`**| Heuristic check of login IP/UA vs current request. | `if(is_session_hijacked()) logout();`|
| **`force_logout(): void`** | Invalidates session, regens ID, and logs out the user. | `force_logout();` |
| **`is_2fa_enabled(user)`** | Boolean check if 2FA secret is set for the user model. | `if(is_2fa_enabled()) ...` |
| **`generate_nonce(): str`**| Generates a cryptographically strong single-use token. | `$n = generate_nonce();` |
| **`verify_nonce(n): bool`**| Consumes the nonce from cache to prevent replay attacks. | `if(verify_nonce($req->n))...`|
| **`get_session_entropy()`** | Measures the randomness of the session ID itself. | `$e = get_session_entropy();` |

---

## 📡 9. API Security
Specific protections for high-traffic REST and GraphQL endpoints.

| Function | How it Works | Example Usage |
| :--- | :--- | :--- |
| **`verify_api_signature()`**| Validates HMAC signature against payload and secret. | `verify_api_signature($p, $s, $k);`|
| **`get_api_limit(): int`** | Fetches dynamic rate limit from CyberShield config. | `$limit = get_api_limit();` |
| **`is_api_abused(): bool`** | Check if current visitor exceeds API abuse threshold. | `if(is_api_abused()) block();` |
| **`verify_jwt_token(t)`** | Structural validation of JWT dot-separated segments. | `verify_jwt_token($token);` |
| **`get_bearer_token()`** | Facade-level extraction of the Bearer string. | `$token = get_bearer_token();` |

---

## ⚙️ 10. Laravel & Environment
Global health and state auditing utilities.

| Function | Logic | Example Usage |
| :--- | :--- | :--- |
| **`is_debug_mode(): bool`** | Direct check of `app.debug` configuration. | `if(is_debug_mode()) alert();` |
| **`is_env_secure(): bool`** | Verifies PROD env + Non-debug + App Key presence. | `if(!is_env_secure()) ...` |
| **`is_maintenance_mode()`**| Check if `php artisan down` is active. | `is_maintenance_mode();` |
| **`is_ssl_active(): bool`** | Verifies HTTPS/SSL termination for the request. | `if(!is_ssl_active()) ...` |
| **`is_db_connected(): bool`**| Performs a PING to the DB PDO connection. | `if(is_db_connected()) ...` |

---

## 🚀 Pro-Tip: Usage in Blade

Since these are global helpers, you can use them directly in your templates to create dynamic, security-aware UIs:

```blade
{{-- Hide sensitive buttons from high-risk users --}}
@if(!is_high_risk())
    <button class="btn btn-danger">Manage Wallet</button>
@endif

{{-- GDPR Compliant Masking --}}
<p>Registered Email: {{ mask_email(auth()->user()->email) }}</p>

{{-- Threat Reputation Badge --}}
<span class="badge badge-{{ ip_reputation() === 'Trusted' ? 'success' : 'warning' }}">
    Trust Level: {{ ip_reputation() }}
</span>
```

[Go back to README.md](../README.md)

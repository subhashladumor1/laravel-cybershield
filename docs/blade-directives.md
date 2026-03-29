# 🎭 Blade Security Directives

CyberShield brings the full power of the security kernel directly into your Blade templates with **100+ `@secure*` directives** — zero PHP code needed in your views.

---

## How Directives Work

CyberShield registers directives automatically via the `CyberShieldServiceProvider`. There are two types:

| Type | Behavior | Usage |
|------|----------|-------|
| **Conditional** (`Blade::if`) | Wraps content in an if-block | `@secureAdmin ... @else ... @endsecureAdmin` |
| **Output** (`Blade::directive`) | Renders/escapes a value in-place | `@secureMaskEmail($user->email)` |

---

## 📋 Complete Directive Reference

### Group 1: Authentication & Session (20 directives)

These directives control UI visibility based on the user's session state and security posture.

| Directive | Logic | Use Case |
|-----------|-------|----------|
| `@secureAuth` | User is logged in **AND** session is not hijacked | Safe content wrapper for authenticated sections |
| `@secureGuest` | User is not logged in | Show login prompts |
| `@secureAdmin` | Logged in **AND** `role === 'admin'` | Admin-only UI elements |
| `@secureUser` | Logged in **AND** `role === 'user'` | Standard user content |
| `@secureRole($role)` | Logged in **AND** role matches `$role` | Any role-based content |
| `@securePermission($perm)` | `user->can($perm)` | Fine-grained permission checks |
| `@secureVerified` | Email is verified | Verified-user-only features |
| `@secure2fa` | 2FA is enabled for current user | Restrict sensitive actions to 2FA users |
| `@secureToken` | Auth check + Bearer token present | API token presence check in views |
| `@secureSession` | Session is started | Session-dependent content |
| `@secureLogin` | User is authenticated | Alias for `@secureAuth` (simpler) |
| `@secureLogout` | User is guest | Alias for `@secureGuest` |
| `@secureAccountLocked` | `user->is_locked` is truthy | Show locked account message |
| `@securePasswordExpired` | `user->password_expired` is truthy | Prompt for password reset |
| `@secureSuspiciousLogin` | `session('suspicious_login')` is set | Warn about suspicious activity |
| `@secureTrustedDevice` | Device cookie matches trusted cache | Show sensitive content for known devices |
| `@secureNewDevice` | Device is NOT trusted | Prompt for device registration |
| `@secureCaptchaRequired` | `session('captcha_required')` is set | Show captcha challenge |
| `@secureRiskLogin` | Threat score > 50 | Warn about risky session |
| `@secureSessionValid` | Session is NOT hijacked | Content for verified sessions only |

**Examples:**
```blade
{{-- Basic auth check with hijack protection --}}
@secureAuth
    <div class="user-dashboard">
        Welcome {{ auth()->user()->name }}!
    </div>
@else
    <a href="/login">Sign In</a>
@endsecureAuth

{{-- 2FA gate for sensitive operations --}}
@secure2fa
    <button class="btn-danger">Delete Account</button>
@else
    <div class="alert">
        Please <a href="/2fa/setup">enable 2FA</a> to access this feature.
    </div>
@endsecure2fa

{{-- Role-based content --}}
@secureRole('manager')
    <a href="/admin/reports">View Reports</a>
@endsecureRole

{{-- New device warning --}}
@secureNewDevice
    <div class="alert alert-info">
        🔔 You're signing in from an unrecognized device. 
        <a href="/verify-device">Verify this device</a>
    </div>
@endsecureNewDevice
```

---

### Group 2: Request & Identity Directives (20 directives)

Control content based on the visitor's network origin and identity signals.

| Directive | Logic | Use Case |
|-----------|-------|----------|
| `@secureIp($ip)` | `real_ip() === $ip` | Content for a specific IP only |
| `@secureCountry($code)` | `ip_country_code() === $code` | Geo-specific content (e.g., country-specific ToS) |
| `@secureRegion($region)` | `ip_region() === $region` | Region-specific promotions |
| `@secureDevice($device)` | UA contains `$device` string | Device-specific UI (mobile/desktop) |
| `@secureFingerprint($fp)` | Request fingerprint matches `$fp` | Fingerprint-locked content |
| `@secureUserAgent($ua)` | UA contains `$ua` string | Browser-specific features |
| `@secureBot` | `is_bot()` is true | Content for identified bots |
| `@secureCrawler` | `is_crawler()` is true | Content for search spiders |
| `@secureProxy` | `is_proxy_ip()` is true | Warning for proxy users |
| `@secureTor` | `is_tor_ip()` is true | Message for TOR users |
| `@secureDatacenter` | `is_datacenter_ip()` is true | Alert for datacenter traffic |
| `@secureTrustedIp` | IP is in whitelist | Bypass UI for internal IPs |
| `@secureSuspiciousIp` | Reputation is 'Suspicious' | Extra verification UI |
| `@secureHighRiskIp` | Threat score ≥ 75 | Emergency lockdown UI |
| `@secureRequestLimit` | `is_api_abused()` is true | Rate limit warning |
| `@secureRequestValid` | No malicious payload in URL/body | Safe content container |
| `@secureRequestSecure` | HTTPS is active | HTTPS-required features |
| `@secureHttps` | HTTPS is active | Alias for `@secureRequestSecure` |
| `@secureApiRequest` | Request expects JSON | API-specific response blocks |
| `@secureAjax` | AJAX request (`X-Requested-With`) | AJAX-specific content |

**Examples:**
```blade
{{-- Country-specific content --}}
@secureCountry('US')
    <div class="promo-us">
        🇺🇸 Exclusive US offer: Get 20% off this month!
    </div>
@endsecureCountry

{{-- TOR user message --}}
@secureTor
    <div class="alert alert-warning">
        You are accessing this site via TOR. Some features are restricted.
    </div>
@endsecureTor

{{-- HTTPS only content --}}
@secureHttps
    <a href="/download/secure-report.pdf">Download Report</a>
@else
    <p>Secure connection required to access downloads.</p>
@endsecureHttps

{{-- Suspicious IP — require captcha --}}
@secureSuspiciousIp
    @include('components.captcha-challenge')
@endsecureSuspiciousIp
```

---

### Group 3: Threat Level Directives (20 directives)

Dynamically adapt your UI based on the current threat environment.

| Directive | Logic | Use Case |
|-----------|-------|----------|
| `@secureThreat` | Threat score > 0 | Any threat present |
| `@secureThreatLow` | Threat score > 10 | Low-level warning |
| `@secureThreatMedium` | Threat score > 40 | Caution banner |
| `@secureThreatHigh` | Threat score > 70 | High alert banner |
| `@secureThreatCritical` | Threat score > 90 | Emergency lockdown UI |
| `@secureAttackDetected` | `is_threat_active()` | Global attack mode UI |
| `@secureBotAttack` | `is_bot() && is_threat_active()` | Bot attack alert |
| `@secureBruteForce` | `is_api_abused()` | Brute force warning |
| `@secureSqlAttack` | `session('sql_attack')` | SQL injection alert |
| `@secureXssAttack` | `session('xss_attack')` | XSS attack alert |
| `@secureSpamAttack` | `session('spam_attack')` | Spam activity alert |
| `@secureFloodAttack` | IP velocity > 200 | Flood attack warning |
| `@secureSuspiciousRequest` | Reputation is 'Suspicious' | Suspicious request alert |
| `@secureSecurityAlert` | `Cache::has('security_alert')` | System-wide alert banner |
| `@secureBlockedIp` | IP is blacklisted | Blocked access message |
| `@secureAllowedIp` | IP is whitelisted | Trusted access content |
| `@secureSecurityMode` | Shield mode is 'secure' | Security mode UI indicator |
| `@secureEmergencyMode` | Emergency mode enabled | Emergency overlay |
| `@secureProtectionEnabled` | CyberShield enabled | Protection status indicator |
| `@secureFirewallActive` | Firewall module enabled | Firewall status indicator |

**Examples:**
```blade
{{-- Progressive threat level banners --}}
@secureThreatLow
    <div class="alert-yellow">
        ℹ️ We've noticed some unusual activity on your account.
    </div>
@endsecureThreatLow

@secureThreatHigh
    <div class="alert-red">
        ⚠️ High-risk activity detected. Certain features have been restricted.
        <a href="/security/review">Review Activity</a>
    </div>
@endsecureThreatHigh

@secureThreatCritical
    <div class="emergency-overlay">
        🚨 Your account has been flagged for emergency review.
        Please contact support immediately.
    </div>
@endsecureThreatCritical

{{-- System-wide security alert (set via Cache::put('security_alert', true)) --}}
@secureSecurityAlert
    <div class="site-banner banner-critical">
        🚨 We are investigating a security incident. 
        Please change your password as a precaution.
    </div>
@endsecureSecurityAlert
```

---

### Group 4: Data Masking & Protection Directives (20 directives)

Protect PII in your views without writing PHP logic.

**Output Directives** (render values directly):

| Directive | Output Example | Use Case |
|-----------|---------------|----------|
| `@secureMaskEmail($email)` | `jo*****@gmail.com` | User profile displays |
| `@secureMaskPhone($phone)` | `555****09` | Contact info |
| `@secureMaskCard($card)` | `************4242` | Payment screens |
| `@secureMaskToken($token)` | `a3b2c1...****...f9e8` | Token display |
| `@secureEncrypt($data)` | Encrypted string | Hidden encrypted fields |
| `@secureDecrypt($data)` | Original value | Decrypted display |
| `@secureTokenField` | `<input name="_token" ...>` | CSRF token field |
| `@secureNonceField` | `<input name="_nonce" ...>` | One-time nonce field |
| `@secureCsrfEnhanced` | CSRF field | Enhanced CSRF protection |
| `@secureHoneypot` | Hidden trap input | Bot trap field |
| `@secureSecureInput($name)` | `<input class="secure-input">` | Styled secure input |
| `@secureEncryptedField($name)` | Hidden marker field | Encrypted field marker |
| `@secureSafeOutput($data)` | Stripped HTML | Safe HTML rendering |
| `@secureSanitize($data)` | HTML-escaped string | Escaped output |
| `@secureEscape($data)` | HTML-escaped string | Raw escape |
| `@secureSafeHtml($data)` | Safe HTML | Formatted content display |
| `@secureSafeScript($data)` | Escaped for JS | Script-safe data |

**Conditional Directives:**

| Directive | Logic | Use Case |
|-----------|-------|----------|
| `@secureSensitive` | Authenticated + 2FA + not high risk | Wrap ultra-sensitive data |
| `@securePrivateData` | Authenticated + (2FA or trusted device) | Private user data |
| `@secureProtectedData` | Authenticated + session valid + not malicious | Protected content wrapper |
| `@secureHidden` | Debug mode OR admin | Developer-only content |

**Examples:**
```blade
{{-- PII display in a user profile --}}
<div class="profile-info">
    <label>Email</label>
    <span>@secureMaskEmail($user->email)</span>

    <label>Phone</label>
    <span>@secureMaskPhone($user->phone)</span>

    <label>Payment Card</label>
    <span>@secureMaskCard($user->card_number)</span>
</div>

{{-- Secure form with all protections --}}
<form method="POST" action="/checkout">
    @secureTokenField
    @secureNonceField
    @secureHoneypot

    <input type="text" name="card" placeholder="Card Number">
    
    @secureSensitive
        <div class="sensitive-section">
            {{-- Only shown to 2FA-enabled, low-risk users --}}
            <input type="text" name="cvv" placeholder="CVV">
        </div>
    @endsecureSensitive

    <button type="submit">Pay Now</button>
</form>

{{-- Safe HTML rendering (user-generated content) --}}
<div class="comment">
    @secureSafeHtml($comment->body)
</div>
```

---

### Group 5: UI Security & Feature Flags (20 directives)

Control UI feature availability and security mode indicators.

| Directive | Logic | Use Case |
|-----------|-------|----------|
| `@secureAdminPanel` | Admin role check | Admin panel wrapper |
| `@secureUserPanel` | Auth + not high risk + no hijack | Standard user panel |
| `@secureRestricted` | Auth + (whitelisted OR trusted device) | IP/device-restricted content |
| `@securePremium` | Auth + `user->is_premium` | Premium feature gate |
| `@secureInternal` | Private IP range | Internal tool UI |
| `@secureAuditVisible` | Auth + `user->can('view_audit')` | Audit log viewer |
| `@secureDebugMode` | `app.debug === true` | Developer debug panels |
| `@secureProductionOnly` | Environment is `production` | Production-only features |
| `@secureMaintenance` | App is in maintenance | Maintenance notices |
| `@secureSecurityBanner` | Config shows banner | Security status bar |
| `@secureSecurityWarning` | Threat score > 30 | Soft warning banner |
| `@secureRiskUser` | `user->risk_score > 50` | High-risk user indicators |
| `@secureRiskIp` | IP threat ≥ 75 | IP risk indicator |
| `@secureSecureMode` | SSL is active | SSL status badge |
| `@secureProtectedRoute` | Route is not null | Route-aware content |
| `@secureProtectedComponent` | Auth + not bot + SSL + not high risk | Full-protection wrapper |
| `@secureFeatureFlag($flag)` | `config("cybershield.features.$flag")` | Feature toggle system |
| `@secureBetaFeature` | Non-production environment | Beta feature preview |
| `@secureSystemAlert` | `Cache::has('system_alert')` | System messages |
| `@secureSystemSafe` | No active attack mode | Normal operation indicator |

**Examples:**
```blade
{{-- Internal tool available only from office IP range --}}
@secureInternal
    <a href="/admin/database-console">🗄️ DB Console</a>
@endsecureInternal

{{-- Feature flag for experimental features --}}
@secureFeatureFlag('dark_mode')
    <button id="toggle-dark-mode">🌙 Dark Mode</button>
@endsecureFeatureFlag

{{-- Debug info for developers --}}
@secureDebugMode
    <div class="debug-panel">
        <pre>Request fingerprint: {{ get_request_fingerprint() }}</pre>
        <pre>Threat score: {{ get_threat_score() }}</pre>
        <pre>IP: {{ real_ip() }} ({{ ip_reputation() }})</pre>
    </div>
@endsecureDebugMode

{{-- Premium gating --}}
@securePremium
    <button>Export to PDF</button>
@else
    <div class="upgrade-prompt">
        Upgrade to Premium to export reports.
    </div>
@endsecurePremium
```

---

## 🏗️ Full Real-World Example: Secure User Profile Page

This example demonstrates a complete, production-grade user profile page using multiple directive categories:

```blade
{{-- resources/views/profile/index.blade.php --}}
<!DOCTYPE html>
<html>
<head><title>My Profile</title></head>
<body>

{{-- System-wide alerts always show at top --}}
@secureSecurityAlert
    <div class="site-alert">
        🚨 Security Notice: We detected unusual activity. 
        Please verify your account.
    </div>
@endsecureSecurityAlert

{{-- Full auth check with session integrity --}}
@secureAuth
    <div class="profile-container">

        {{-- Threat level responsive banner --}}
        @secureThreatHigh
            <div class="alert alert-danger">
                ⚠️ Unusual activity detected from your location.
            </div>
        @endsecureThreatHigh

        @secureNewDevice
            <div class="alert alert-info">
                📱 Unrecognized device. 
                <a href="/verify-device">Verify it</a> for full access.
            </div>
        @endsecureNewDevice

        <h1>Welcome, {{ auth()->user()->name }}</h1>

        {{-- PII displayed safely --}}
        <table class="info-table">
            <tr>
                <td>Email</td>
                <td>@secureMaskEmail(auth()->user()->email)</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>@secureMaskPhone(auth()->user()->phone)</td>
            </tr>
            <tr>
                <td>API Token</td>
                <td>@secureMaskToken(auth()->user()->api_token)</td>
            </tr>
        </table>

        {{-- Update form --}}
        <form method="POST" action="/profile/update">
            @secureTokenField
            @secureNonceField
            @secureHoneypot

            <input type="email" name="email" value="{{ auth()->user()->email }}">

            {{-- 2FA gate for sensitive changes --}}
            @secure2fa
                <button type="submit" class="btn-primary">Update Profile</button>
            @else
                <p class="warning">
                    Enable <a href="/2fa/setup">Two-Factor Authentication</a> 
                    to edit your profile.
                </p>
            @endsecure2fa
        </form>

        {{-- Admin section --}}
        @secureAdmin
            <div class="admin-tools">
                <h3>Admin Tools</h3>
                <a href="/admin/users">User Management</a>
                
                @secureDebugMode
                    <div class="debug">
                        Threat Score: {{ get_threat_score() }} | 
                        IP: {{ real_ip() }} | 
                        Rep: {{ ip_reputation() }}
                    </div>
                @endsecureDebugMode
            </div>
        @endsecureAdmin

    </div>
@else
    <p>Please <a href="/login">log in</a> to view your profile.</p>
@endsecureAuth

</body>
</html>
```

---

[← Back to Middleware Catalog](middleware.md) | [Next: Artisan Commands →](commands.md)

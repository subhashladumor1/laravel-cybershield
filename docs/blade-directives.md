# 🎨 Blade Directives Reference

Integrate security layers seamlessly into your frontend with **100+ specialized Blade directives**. These allow for declarative, logic-driven UI protection.

---

## 🔐 1. Authentication Directives (20)
Control UI elements based on identity, roles, and session integrity.

| Directive | Description | Example |
|-----------|-------------|---------|
| `@secureAuth` | Shown if user is authenticated and session is not hijacked. | `@secureAuth` Welcome! `@endsecureAuth` |
| `@secureGuest` | Shown only to unauthenticated visitors. | `@secureGuest` Please log in. `@endsecureGuest` |
| `@secureAdmin` | Shown only if `user->role === 'admin'`. | `@secureAdmin` Admin Panel `@endsecureAdmin` |
| `@secureUser` | Shown only if `user->role === 'user'`. | `@secureUser` User Dashboard `@endsecureUser` |
| `@secureRole($role)` | Conditional based on a specific role. | `@secureRole('manager')` Manager View `@endsecureRole` |
| `@securePermission($p)` | Checks if user has a specific permission. | `@securePermission('delete')` Delete `@endsecurePermission` |
| `@secureVerified` | Shown if user email is verified. | `@secureVerified` Verified badge `@endsecureVerified` |
| `@secure2fa` | Shown if 2FA is enabled for the current user. | `@secure2fa` 2FA Active `@endsecure2fa` |
| `@secureToken` | Shown if request has a valid Bearer token. | `@secureToken` API Access On `@endsecureToken` |
| `@secureSessionValid` | Ensures session hasn't been hijacked. | `@secureSessionValid` Session Safe `@endsecureSessionValid` |
| `@secureAccountLocked` | Shown if user account is marked as locked. | `@secureAccountLocked` Account Locked `@endsecureAccountLocked` |
| `@securePasswordExpired`| Shown if user needs to reset their password. | `@securePasswordExpired` Update Required `@endsecurePasswordExpired` |
| `@secureSuspiciousLogin`| Shown if a login from a new IP/UA occurred. | `@secureSuspiciousLogin` New Login! `@endsecureSuspiciousLogin` |
| `@secureTrustedDevice` | Shown if the device is recognized/trusted. | `@secureTrustedDevice` Trusted `@endsecureTrustedDevice` |

---

## 🌐 2. Request Security Directives (20)
Adapt content based on network, geography, and device profile.

| Directive | Description | Example |
|-----------|-------------|---------|
| `@secureIp($ip)` | Shown if the visitor matches a specific IP. | `@secureIp('127.0.0.1')` Local Dev `@endsecureIp` |
| `@secureCountry($code)` | Filter content by ISO country code. | `@secureCountry('US')` US Only Deal `@endsecureCountry` |
| `@secureBot` | Shown to automated scripts/bots. | `@secureBot` <!-- Bot trap --> `@endsecureBot` |
| `@secureCrawler` | Shown only to search engine crawlers. | `@secureCrawler` SEO Meta `@endsecureCrawler` |
| `@secureTor` | Shown if user is accessing via TOR exit node. | `@secureTor` Restricted for Tor `@endsecureTor` |
| `@secureProxy` | Detected if request uses a proxy/VPN. | `@secureProxy` VPN Detected `@endsecureProxy` |
| `@secureHttps` | Ensures render only on SSL connections. | `@secureHttps` SSL Active `@endsecureHttps` |
| `@secureAjax` | Shown if request is an AJAX call. | `@secureAjax` JSON Fragment `@endsecureAjax` |
| `@secureHighRiskIp` | Triggered if IP has a threat score > 70. | `@secureHighRiskIp` Verify Identity `@endsecureHighRiskIp` |

---

## ⚡ 3. Threat Detection Directives (20)
Real-time UI response to security events and system modes.

| Directive | Description | Example|
|-----------|-------------|--------|
| `@secureAttackDetected` | Shown if the system is in global attack mode. | `@secureAttackDetected` High Alert `@endsecureAttackDetected` |
| `@secureThreatHigh` | Shown if current threat score is > 70. | `@secureThreatHigh` CAPTCHA Req `@endsecureThreatHigh` |
| `@secureBruteForce` | Shown if request rate limit is exceeded. | `@secureBruteForce` Cooling down... `@endsecureBruteForce` |
| `@secureFirewallActive` | Checks if CyberShield WAF is enabled. | `@secureFirewallActive` WAF High `@endsecureFirewallActive` |
| `@secureEmergencyMode` | Shown if site is in emergency lockdown. | `@secureEmergencyMode` Site Lock `@endsecureEmergencyMode` |
| `@secureBlockedIp` | Shown if the user's IP is currently blacklisted. | `@secureBlockedIp` Access Denied `@endsecureBlockedIp` |

---

## 🛡️ 4. Data Security Directives (20)
Masking and sanitization for sensitive information formatting.

| Directive/Tag | Description | Example |
|---------------|-------------|---------|
| `@secureMaskEmail($e)` | Masks email (e.g., `te***@domain.com`). | `@secureMaskEmail($user->email)` |
| `@secureMaskPhone($p)` | Masks phone numbers for PII safety. | `@secureMaskPhone($user->phone)` |
| `@secureMaskCard($c)` | Masks credit card number (last 4 only). | `@secureMaskCard($card)` |
| `@secureEncrypt($d)` | Encrypts data before showing in HTML. | `@secureEncrypt($secretId)` |
| `@secureDecrypt($d)` | Decrypts for secure client-side usage. | `@secureDecrypt($payload)` |
| `@secureNonceField` | Generates a secure one-time nonce input. | `<form> @secureNonceField </form>` |
| `@secureSafeHtml($d)` | Strips dangerous tags (XSS protection). | `@secureSafeHtml($userBio)` |
| `@secureSanitize($d)`| Escapes characters for safe output. | `@secureSanitize($input)` |

---

## 🖥️ 5. UI Security Directives (20)
Granular control over environment-specific and risk-aware UI.

| Directive | Description | Example |
|-----------|-------------|---------|
| `@secureSensitive` | Auth + 2FA + Low Risk required. | `@secureSensitive` Wallet Info `@endsecureSensitive` |
| `@securePrivateData` | Auth + (2FA or Trusted Device) required. | `@securePrivateData` SSN Number `@endsecurePrivateData` |
| `@secureInternal` | Only renders if IP is a private/local range. | `@secureInternal` Internal Log `@endsecureInternal` |
| `@secureDebugMode` | Shown if `app.debug` is true. | `@secureDebugMode` Debug Info `@endsecureDebugMode` |
| `@secureProductionOnly`| Shown if environment is 'production'. | `@secureProductionOnly` Analytics `@endsecureProductionOnly` |
| `@secureMaintenance` | Shown if site is in maintenance mode. | `@secureMaintenance` Offline Alert `@endsecureMaintenance` |
| `@secureFeatureFlag($f)`| Checks for specific flag in cybershield config. | `@secureFeatureFlag('beta')` Beta `@endsecureFeatureFlag` |
| `@secureProtectedComponent` | Checks for SSL + Human + Low Risk + Auth. | `@secureProtectedComponent` <LivePlayer /> `@endsecureProtectedComponent` |

---

## 🛠️ Implementation Details
All block directives (`@secure...`) automatically support corresponding `@else` and `@endsecure...` tags.

Example:
```blade
@secureAdmin
    <a href="/admin">Dashboard</a>
@else
    <a href="/profile">My Settings</a>
@endsecureAdmin
```

[Go back to README.md](../README.md)

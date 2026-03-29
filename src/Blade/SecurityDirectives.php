<?php

namespace CyberShield\Blade;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;

class SecurityDirectives
{
    public function register()
    {
        // --- 1. AUTHENTICATION DIRECTIVES (20) ---
        Blade::if('secureAuth', fn() => auth()->check() && !is_session_hijacked()); // Base auth with session integrity
        Blade::if('secureGuest', fn() => auth()->guest());
        Blade::if('secureAdmin', fn() => auth()->check() && optional(auth()->user())->role === 'admin');
        Blade::if('secureUser', fn() => auth()->check() && optional(auth()->user())->role === 'user');
        Blade::if('secureRole', fn($role) => auth()->check() && optional(auth()->user())->role == $role);
        Blade::if('securePermission', fn($permission) => auth()->check() && auth()->user()->can($permission));
        Blade::if('secureVerified', fn() => auth()->check() && auth()->user()->hasVerifiedEmail());
        Blade::if('secure2fa', fn() => is_2fa_enabled());
        Blade::if('secureToken', fn() => auth()->check() && Request::bearerToken() !== null);
        Blade::if('secureSession', fn() => session()->isStarted());
        Blade::if('secureLogin', fn() => auth()->check());
        Blade::if('secureLogout', fn() => auth()->guest());
        Blade::if('secureAccountLocked', fn() => auth()->check() && optional(auth()->user())->is_locked);
        Blade::if('securePasswordExpired', fn() => auth()->check() && optional(auth()->user())->password_expired);
        Blade::if('secureSuspiciousLogin', fn() => session()->has('suspicious_login'));
        Blade::if('secureTrustedDevice', fn() => is_trusted_device());
        Blade::if('secureNewDevice', fn() => !is_trusted_device());
        Blade::if('secureCaptchaRequired', fn() => session()->has('captcha_required'));
        Blade::if('secureRiskLogin', fn() => get_threat_score() > 50);
        Blade::if('secureSessionValid', fn() => !is_session_hijacked());

        // --- 2. REQUEST SECURITY DIRECTIVES (20) ---
        Blade::if('secureIp', fn($ip) => real_ip() === $ip);
        Blade::if('secureCountry', fn($code) => ip_country_code() === strtoupper($code));
        Blade::if('secureRegion', fn($region) => ip_region() === $region);
        Blade::if('secureDevice', fn($device) => str_contains(strtolower(get_user_agent()), strtolower($device)));
        Blade::if('secureFingerprint', fn($fp) => get_request_fingerprint() === $fp);
        Blade::if('secureUserAgent', fn($ua) => str_contains(get_user_agent(), $ua));
        Blade::if('secureBot', fn() => is_bot());
        Blade::if('secureCrawler', fn() => is_crawler());
        Blade::if('secureProxy', fn() => is_proxy_ip());
        Blade::if('secureTor', fn() => is_tor_ip());
        Blade::if('secureDatacenter', fn() => is_datacenter_ip());
        Blade::if('secureTrustedIp', fn() => ip_is_whitelisted());
        Blade::if('secureSuspiciousIp', fn() => ip_reputation() === 'Suspicious');
        Blade::if('secureHighRiskIp', fn() => is_high_risk());
        Blade::if('secureRequestLimit', fn() => is_api_abused());
        Blade::if('secureRequestValid', fn() => !is_malicious_payload(request()->fullUrl()) && !is_malicious_payload(request()->getContent()));
        Blade::if('secureRequestSecure', fn() => is_ssl_active());
        Blade::if('secureHttps', fn() => is_ssl_active());
        Blade::if('secureApiRequest', fn() => request()->expectsJson());
        Blade::if('secureAjax', fn() => request()->ajax());

        // --- 3. THREAT DETECTION DIRECTIVES (20) ---
        Blade::if('secureThreat', fn() => get_threat_score() > 0);
        Blade::if('secureThreatLow', fn() => get_threat_score() > 10);
        Blade::if('secureThreatMedium', fn() => get_threat_score() > 40);
        Blade::if('secureThreatHigh', fn() => get_threat_score() > 70);
        Blade::if('secureThreatCritical', fn() => get_threat_score() > 90);
        Blade::if('secureAttackDetected', fn() => is_threat_active());
        Blade::if('secureBotAttack', fn() => is_bot() && is_threat_active());
        Blade::if('secureBruteForce', fn() => is_api_abused());
        Blade::if('secureSqlAttack', fn() => session()->has('sql_attack'));
        Blade::if('secureXssAttack', fn() => session()->has('xss_attack'));
        Blade::if('secureSpamAttack', fn() => session()->has('spam_attack'));
        Blade::if('secureFloodAttack', fn() => get_ip_velocity() > 200);
        Blade::if('secureSuspiciousRequest', fn() => ip_reputation() === 'Suspicious');
        Blade::if('secureSecurityAlert', fn() => Cache::has('security_alert'));
        Blade::if('secureBlockedIp', fn() => ip_is_blacklisted());
        Blade::if('secureAllowedIp', fn() => ip_is_whitelisted());
        Blade::if('secureSecurityMode', fn() => shield_config('mode') === 'secure');
        Blade::if('secureEmergencyMode', fn() => shield_config('emergency_mode', false));
        Blade::if('secureProtectionEnabled', fn() => shield_config('enabled', true));
        Blade::if('secureFirewallActive', fn() => shield_config('firewall', true));

        // --- 4. DATA SECURITY DIRECTIVES (20) ---
        Blade::directive('secureMaskEmail', fn($e) => "<?php echo mask_email($e); ?>");
        Blade::directive('secureMaskPhone', fn($p) => "<?php echo mask_phone($p); ?>");
        Blade::directive('secureMaskCard', fn($c) => "<?php echo mask_card($c); ?>");
        Blade::directive('secureMaskToken', fn($t) => "<?php echo mask_token($t); ?>");
        Blade::directive('secureEncrypt', fn($d) => "<?php echo secure_encrypt($d); ?>");
        Blade::directive('secureDecrypt', fn($d) => "<?php echo secure_decrypt($d); ?>");
        Blade::if('secureSensitive', fn() => auth()->check() && is_2fa_enabled() && !is_high_risk());
        Blade::if('securePrivateData', fn() => auth()->check() && (is_2fa_enabled() || is_trusted_device()));
        Blade::if('secureProtectedData', fn() => auth()->check() && !is_session_hijacked() && ip_reputation() !== 'Malicious');
        Blade::if('secureHidden', fn() => is_debug_mode() || (auth()->check() && optional(auth()->user())->role === 'admin'));
        Blade::directive('secureTokenField', fn() => "<?php echo csrf_field(); ?>");
        Blade::directive('secureNonceField', fn() => "<?php echo '<input type=\"hidden\" name=\"_nonce\" value=\"' . generate_nonce() . '\">'; ?>");
        Blade::directive('secureCsrfEnhanced', fn() => "<?php echo csrf_field(); ?>");
        Blade::directive('secureSecureInput', fn($name) => "<?php echo '<input type=\"text\" name=\"' . $name . '\" class=\"secure-input\">'; ?>");
        Blade::directive('secureEncryptedField', fn($name) => "<?php echo '<input type=\"hidden\" name=\"' . $name . '_enc\" value=\"1\">'; ?>");
        Blade::directive('secureSafeOutput', fn($d) => "<?php echo sanitize_html($d); ?>");
        Blade::directive('secureSanitize', fn($d) => "<?php echo sanitize_string($d); ?>");
        Blade::directive('secureEscape', fn($d) => "<?php echo htmlspecialchars($d, ENT_QUOTES, 'UTF-8'); ?>");
        Blade::directive('secureSafeHtml', fn($d) => "<?php echo sanitize_html($d); ?>");
        Blade::directive('secureSafeScript', fn($d) => "<?php echo sanitize_string($d); ?>");
        Blade::directive('secureHoneypot', function () {
            $fieldName = config('cybershield.bot_protection.honeypot.field_name', 'hp_token_id');
            return "<?php echo '<div style=\"display:none\"><input type=\"text\" name=\"' . '$fieldName' . '\" value=\"\"></div>'; ?>";
        });

        // --- 5. UI SECURITY DIRECTIVES (20) ---
        Blade::if('secureAdminPanel', fn() => auth()->check() && optional(auth()->user())->role === 'admin');
        Blade::if('secureUserPanel', fn() => auth()->check() && !is_high_risk() && !is_session_hijacked());
        Blade::if('secureRestricted', fn() => auth()->check() && (ip_is_whitelisted() || is_trusted_device()));
        Blade::if('securePremium', fn() => auth()->check() && optional(auth()->user())->is_premium);
        Blade::if('secureInternal', fn() => is_private_ip());
        Blade::if('secureAuditVisible', fn() => auth()->check() && auth()->user()->can('view_audit'));
        Blade::if('secureDebugMode', fn() => is_debug_mode());
        Blade::if('secureProductionOnly', fn() => app()->environment('production'));
        Blade::if('secureMaintenance', fn() => is_maintenance_mode());
        Blade::if('secureSecurityBanner', fn() => shield_config('show_banner', true));
        Blade::if('secureSecurityWarning', fn() => get_threat_score() > 30);
        Blade::if('secureRiskUser', fn() => auth()->check() && optional(auth()->user())->risk_score > 50);
        Blade::if('secureRiskIp', fn() => is_high_risk());
        Blade::if('secureSecureMode', fn() => is_ssl_active());
        Blade::if('secureProtectedRoute', fn() => request()->route() !== null);
        Blade::if('secureProtectedComponent', fn() => auth()->check() && !is_bot() && is_ssl_active() && !is_high_risk());

        Blade::if('secureFeatureFlag', fn($flag) => shield_config("features.$flag", false));
        Blade::if('secureBetaFeature', fn() => !app()->environment('production'));
        Blade::if('secureSystemAlert', fn() => Cache::has('system_alert'));
        Blade::if('secureSystemSafe', fn() => !is_threat_active());
    }
}

<?php

namespace CyberShield\Core;

use Illuminate\Http\Request;
use CyberShield\Security\Firewall\WAFEngine;
use CyberShield\Security\Bot\BotDetector;
use CyberShield\Security\RateLimiting\AdvancedRateLimiter;
use CyberShield\Security\Api\ApiSecurityManager;
use CyberShield\Security\Threat\ThreatEngine;
use CyberShield\Security\Database\DatabaseIntrusionDetector;
use CyberShield\Security\Network\NetworkGuard;
use CyberShield\Exceptions\SecurityException;

class SecurityKernel
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Run the request through the security pipeline.
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!config('cybershield.enabled')) {
            return $next($request);
        }

        // 1. Network Security (IP, Country, TOR, VPN)
        $this->resolve(NetworkGuard::class)->check($request);

        // 2. Request Validation
        $this->validateRequest($request);

        // 3. Bot Detection
        if (config('cybershield.modules.bot_detection')) {
            $this->resolve(BotDetector::class)->analyze($request);
        }

        // 4. Rate Limiting
        if (config('cybershield.modules.rate_limiting')) {
            $this->resolve(AdvancedRateLimiter::class)->check($request);
        }

        // 5. Firewall Engine (WAF)
        if (config('cybershield.modules.firewall')) {
            $this->resolve(WAFEngine::class)->inspect($request);
        }

        // 6. API Security
        if (config('cybershield.modules.api_security') && $request->is('api/*')) {
            $this->resolve(ApiSecurityManager::class)->verify($request);
        }

        // 7. Threat Detection
        if (config('cybershield.modules.threat_engine')) {
            $this->resolve(ThreatEngine::class)->evaluate($request);
        }

        // 8. Database Guard (registered as listener, but can trigger initial state)
        if (config('cybershield.modules.database_guard')) {
            $this->resolve(DatabaseIntrusionDetector::class)->monitor();
        }

        return $next($request);
    }

    protected function resolve($class)
    {
        return $this->app->make($class);
    }

    protected function validateRequest(Request $request)
    {
        $config = config('cybershield.request_security', []);

        // 1. Enforce HTTPS
        if (($config['enforce_https'] ?? false) && !$request->secure() && !app()->environment('local')) {
            throw new SecurityException("Secure connection (HTTPS) is required.", 403);
        }

        // 2. Check Allowed Origins (CORS-like check at kernel level)
        $origin = $request->header('Origin');
        $allowedOrigins = $config['allowed_origins'] ?? [];
        if ($origin && !empty($allowedOrigins) && !in_array($origin, $allowedOrigins) && !in_array('*', $allowedOrigins)) {
            throw new SecurityException("Origin '{$origin}' is not allowed.", 403);
        }

        // 3. Check Required Headers
        $requiredHeaders = $config['required_headers'] ?? [];
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                throw new SecurityException("Missing required security header: {$header}.", 403);
            }
        }

        // 4. Check Trusted Hosts
        $host = $request->getHost();
        $trustedHosts = $config['trusted_hosts'] ?? [];
        if (!empty($trustedHosts) && !in_array($host, $trustedHosts)) {
             throw new SecurityException("Untrusted host: {$host}.", 403);
        }
    }
}


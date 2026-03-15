<?php

namespace CyberShield\Core;

use Illuminate\Http\Request;
use CyberShield\Firewall\WAFEngine;
use CyberShield\BotDetection\BotDetector;
use CyberShield\RateLimiting\AdvancedRateLimiter;
use CyberShield\ApiSecurity\ApiSecurityManager;
use CyberShield\ThreatDetection\ThreatEngine;
use CyberShield\DatabaseSecurity\DatabaseIntrusionDetector;
use CyberShield\NetworkSecurity\NetworkGuard;

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
        // General request structure validation
    }
}

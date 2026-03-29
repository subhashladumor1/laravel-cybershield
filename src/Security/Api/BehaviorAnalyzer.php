<?php

namespace CyberShield\Security\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BehaviorAnalyzer
{
    /**
     * Analyze request behavior.
     */
    public function analyze(Request $request): void
    {
        $fingerprint = $this->generateFingerprint($request);
        $request->attributes->set('_api_fingerprint', $fingerprint);

        $this->detectAbuse($request, $fingerprint);
        $this->monitorTraffic($request);
    }

    /**
     * Generate a unique fingerprint for the API request.
     */
    protected function generateFingerprint(Request $request): string
    {
        $headers = shield_config('api_security.fingerprint_headers', [
            'User-Agent',
            'Accept-Language',
            'Accept-Encoding',
        ]);

        $fingerprintData = [real_ip()];
        foreach ($headers as $header) {
            $fingerprintData[] = (string) $request->header($header, 'none');
        }
        
        $keyHeader = shield_config('api_security.headers.key', 'X-API-KEY');
        $fingerprintData[] = (string) $request->header($keyHeader, 'no-key');

        return hash('sha256', implode('|', $fingerprintData));
    }

    /**
     * Detect API abuse patterns.
     */
    protected function detectAbuse(Request $request, string $fingerprint): void
    {
        // 1. Detect rapid sequential requests (Velocity)
        $velocityKey = "cybershield:velocity:{$fingerprint}";
        $hits = (int) Cache::increment($velocityKey);
        
        $window = (int) shield_config('api_security.velocity_window', 10);
        if ($hits === 1) {
            Cache::put($velocityKey, 1, $window); // Window from config
        }

        if ($hits > (int) shield_config('api_security.abuse_threshold', 50)) {
            $this->flagAbuse($request, 'High Velocity Requests');
        }

        // 2. Detect unusual path traversal patterns
        if (is_lfi_injection($request->path())) {
            $this->flagAbuse($request, 'Path Traversal Attempt');
        }
    }

    /**
     * Flag a request as abusive.
     */
    protected function flagAbuse(Request $request, string $reason): void
    {
        $request->attributes->set('_api_abuse_detected', true);
        $request->attributes->set('_api_abuse_reason', $reason);

        Log::warning("CyberShield API Abuse Detected: {$reason}", [
            'ip' => real_ip(),
            'url' => $request->fullUrl(),
            'fingerprint' => (string) $request->attributes->get('_api_fingerprint')
        ]);
    }

    /**
     * Monitor API traffic for anomalies.
     */
    protected function monitorTraffic(Request $request): void
    {
        $statsKey = "cybershield:api_stats:" . date('Y-m-d-H');
        Cache::increment($statsKey . ":total_requests");

        if ($request->isMethod('POST')) {
            Cache::increment($statsKey . ":post_requests");
        }
    }
}


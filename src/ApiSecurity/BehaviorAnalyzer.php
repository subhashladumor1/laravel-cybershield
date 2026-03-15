<?php

namespace CyberShield\ApiSecurity;

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
        return hash('sha256', implode('|', [
            real_ip(),
            (string) $request->userAgent(),
            (string) $request->header('Accept-Language'),
            (string) $request->header('Accept-Encoding'),
            (string) $request->header('X-API-KEY', 'no-key')
        ]));
    }

    /**
     * Detect API abuse patterns.
     */
    protected function detectAbuse(Request $request, string $fingerprint): void
    {
        // 1. Detect rapid sequential requests (Velocity)
        $velocityKey = "cybershield:velocity:{$fingerprint}";
        $hits = (int) Cache::increment($velocityKey);
        if ($hits === 1) {
            Cache::put($velocityKey, 1, 10); // 10 second window
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

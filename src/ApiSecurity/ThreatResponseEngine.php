<?php

namespace CyberShield\ApiSecurity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThreatResponseEngine
{
    /**
     * Handle detected threats.
     */
    public function handle(Request $request): void
    {
        if ($request->attributes->get('_api_abuse_detected')) {
            $reason = (string) $request->attributes->get('_api_abuse_reason', 'Threat detected');

            // 1. Log the threat
            $this->logThreat($request, $reason);

            // 2. Block if threshold met (automated blocking)
            if (shield_config('api_security.auto_block', true)) {
                $this->autoBlock($request);
            }

            // 3. Reject the request
            shield_abort(403, "Access Denied: {$reason}", 'ThreatBlocked');
        }
    }

    /**
     * Log threat details.
     */
    protected function logThreat(Request $request, string $reason): void
    {
        $data = [
            'ip' => real_ip(),
            'url' => (string) $request->fullUrl(),
            'reason' => $reason,
            'fingerprint' => (string) $request->attributes->get('_api_fingerprint'),
            'api_key' => $request->header('X-API-KEY'),
            'timestamp' => now()->toDateTimeString()
        ];

        Log::critical("CyberShield API Threat Blocked", $data);

        // Optional: Save to security_logs table via helper if DB logging enabled
        if (shield_config('monitoring.db_logging', true)) {
            log_threat_event('API_THREAT', $data);
        }
    }

    /**
     * Automatically block the IP if abuse is detected.
     */
    protected function autoBlock(Request $request): void
    {
        block_current_ip("Automated API Security Block: " . (string) $request->attributes->get('_api_abuse_reason'));
    }
}

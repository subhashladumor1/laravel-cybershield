<?php

namespace CyberShield\Security\Threat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThreatEngine
{
    public function evaluate(Request $request)
    {
        $ip = $request->ip();
        $this->calculateScore($request);
        
        $currentScore = $this->getIpScore($ip);
        $threshold = (int) shield_config('network_security.threat_score_threshold', 80);

        if ($currentScore >= $threshold) {
            \CyberShield\Logging\SecurityLogger::critical('threat', [
                'status' => 'blocked',
                'ip' => $ip,
                'message' => "IP {$ip} blocked due to high threat score ({$currentScore}).",
            ]);
            
            \CyberShield\Models\BlockedIp::firstOrCreate(
                ['ip' => $ip],
                [
                    'reason' => 'High threat score detected by ThreatEngine',
                    'expires_at' => now()->addDays(7)
                ]
            );
        }
    }

    public function calculateScore(Request $request): int
    {
        $ip = $request->ip();
        $score = 0;
        
        $scoringConfig = (array) shield_config('threat_detection.scoring', [
            'insecure_request' => 10,
            'missing_accept_language' => 20,
            'suspicious_user_agent' => 30,
        ]);

        // 1. Check if request is secure
        if (!$request->secure() && shield_config('request_security.enforce_https', true)) {
            $score += (int) ($scoringConfig['insecure_request'] ?? 10);
        }

        // 2. Check for missing Accept-Language (common in bots)
        if (!$request->header('Accept-Language')) {
            $score += (int) ($scoringConfig['missing_accept_language'] ?? 20);
        }

        // 3. Check for suspicious User-Agent strings
        if ($this->hasSuspiciousUserAgent($request)) {
            $score += (int) ($scoringConfig['suspicious_user_agent'] ?? 30);
        }

        // Increment the score in cache with dynamic TTL
        $ttl = (int) shield_config('threat_detection.score_ttl', 86400);
        $current = $this->getIpScore($ip);
        Cache::put("cybershield:threat_score:{$ip}", $current + $score, $ttl);

        return $current + $score;
    }

    protected function hasSuspiciousUserAgent(Request $request): bool
    {
        $ua = strtolower((string) $request->userAgent());
        $suspicious = ['sqlmap', 'acunetix', 'nikto', 'dirbuster', 'burp', 'metasploit'];
        
        foreach ($suspicious as $s) {
            if (str_contains($ua, $s)) {
                return true;
            }
        }
        return false;
    }

    public function getIpScore($ip)
    {
        return (int) Cache::get("cybershield:threat_score:{$ip}", 0);
    }
}

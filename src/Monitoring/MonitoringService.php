<?php

namespace CyberShield\Monitoring;

use CyberShield\Models\RequestLog;
use CyberShield\Models\IpActivity;
use CyberShield\Models\BlockedIp;
use CyberShield\Models\ThreatLog;
use CyberShield\Models\ApiMetric;
use Illuminate\Support\Facades\Request;

class MonitoringService
{
    /**
     * Log request data to the database.
     */
    public function logRequest(array $data): void
    {
        RequestLog::create($data);
        $this->updateIpActivity($data['ip']);
        
        if (str_starts_with($data['url'], 'api/')) {
            $this->logApiMetric($data['url'], $data['method'], $data['response_time']);
        }
    }

    /**
     * Update or create IP activity record.
     */
    public function updateIpActivity(string $ip): void
    {
        $activity = IpActivity::firstOrCreate(['ip' => $ip]);
        $activity->increment('total_requests');
        $activity->update(['last_seen' => now()]);
    }

    /**
     * Log an API metric.
     */
    public function logApiMetric(string $url, string $method, float $responseTime): void
    {
        $endpoint = explode('?', $url)[0]; // Strip query params
        $metric = ApiMetric::firstOrCreate(
            ['endpoint' => $endpoint, 'method' => $method],
            ['captured_at' => now()]
        );

        $newHits = $metric->hits + 1;
        $newAvg = (($metric->avg_response_time * $metric->hits) + $responseTime) / $newHits;

        $metric->update([
            'hits' => $newHits,
            'avg_response_time' => $newAvg,
            'captured_at' => now()
        ]);
    }

    /**
     * Log a security threat.
     */
    public function logThreat(string $ip, string $type, string $severity, array $details = []): void
    {
        ThreatLog::create([
            'ip' => $ip,
            'threat_type' => $type,
            'severity' => $severity,
            'details' => $details
        ]);

        $this->updateIpThreatScore($ip, $severity);
    }

    /**
     * Update IP threat score based on severity.
     */
    protected function updateIpThreatScore(string $ip, string $severity): void
    {
        $points = match ($severity) {
            'high' => 50,
            'medium' => 20,
            'low' => 5,
            default => 1,
        };

        $activity = IpActivity::firstOrCreate(['ip' => $ip]);
        $activity->increment('threat_score', $points);

        if ($activity->threat_score >= config('cybershield.network_security.threat_score_threshold', 80)) {
            $this->blockIp($ip, 'Automated block: Threat score threshold exceeded');
        }
    }

    /**
     * Block an IP address.
     */
    public function blockIp(string $ip, string $reason = null, int $durationMinutes = null): void
    {
        BlockedIp::updateOrCreate(
            ['ip' => $ip],
            [
                'reason' => $reason,
                'expires_at' => $durationMinutes ? now()->addMinutes($durationMinutes) : null
            ]
        );
    }

    /**
     * Check if an IP address is blocked.
     */
    public function isBlocked(string $ip): bool
    {
        $block = BlockedIp::where('ip', $ip)->first();

        if (!$block) {
            return false;
        }

        if ($block->expires_at && $block->expires_at->isPast()) {
            $block->delete();
            return false;
        }

        return true;
    }
}

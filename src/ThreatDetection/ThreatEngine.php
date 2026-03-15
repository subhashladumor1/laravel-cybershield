<?php

namespace CyberShield\ThreatDetection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThreatEngine
{
    public function evaluate(Request $request)
    {
        $ip = $request->ip();
        $score = $this->calculateScore($request);

        $this->updateIpScore($ip, $score);

        if ($this->getIpScore($ip) >= config('cybershield.threat_engine.threshold')) {
            // Block IP
        }
    }

    protected function calculateScore(Request $request)
    {
        $score = 0;

        // Add points for suspicious attributes
        if (!$request->secure())
            $score += 10;
        if (!$request->header('Accept-Language'))
            $score += 20;

        return $score;
    }

    protected function updateIpScore($ip, $points)
    {
        $current = $this->getIpScore($ip);
        Cache::put("cybershield:threat_score:{$ip}", $current + $points, 86400);
    }

    public function getIpScore($ip)
    {
        return Cache::get("cybershield:threat_score:{$ip}", 0);
    }
}

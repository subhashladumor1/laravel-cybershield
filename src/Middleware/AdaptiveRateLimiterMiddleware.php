<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdaptiveRateLimiterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Check Global & Module State
        if (!shield_config('enabled', true) || !shield_config('modules.rate_limiting', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        $score = ip_threat_score(); $limit = $score > 50 ? 10 : 100; $v = Cache::increment('ratelimit:adaptive:' . real_ip()); if ($v > $limit) shield_abort(429, 'Adaptive Rate Limit Exceeded', 'AdaptiveRateLimiter');

        return $next($request);
    }
}
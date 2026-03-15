<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IpRateLimiterMiddleware
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
        $limit = Cache::increment('ratelimit:ip:' . real_ip()); Cache::expire('ratelimit:ip:' . real_ip(), 60); if ($limit > shield_config('rate_limiting.ip_limit', 100)) shield_abort(429, 'IP Rate Limit Exceeded', 'IpRateLimiter');

        return $next($request);
    }
}
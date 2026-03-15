<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DynamicRateLimiterMiddleware
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
        $load = (float)sys_getloadavg()[0]; $limit = $load > 2.0 ? 20 : 100; if (Cache::increment('ratelimit:dynamic:'.real_ip()) > $limit) shield_abort(429, 'Dynamic Rate Limit Exceeded', 'DynamicRateLimiter');

        return $next($request);
    }
}
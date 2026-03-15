<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CostBasedRateLimiterMiddleware
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
        $cost = $request->is('search') ? 5 : 1; $v = Cache::get('cost:'.real_ip(), 0) + $cost; if ($v > 100) shield_abort(429, 'Resource Cost Limit Exceeded', 'CostBasedRateLimiter'); Cache::put('cost:'.real_ip(), $v, 60);

        return $next($request);
    }
}
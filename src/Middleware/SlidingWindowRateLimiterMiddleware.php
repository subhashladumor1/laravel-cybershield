<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlidingWindowRateLimiterMiddleware
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
        $key = 'sliding_window:' . real_ip(); $now = time(); $history = Cache::get($key, []); $history[] = $now; $history = array_filter($history, fn($t) => $t > ($now - 60)); if (count($history) > 60) shield_abort(429, 'Sliding Window Limit Exceeded', 'SlidingWindowRateLimiter'); Cache::put($key, $history, 60);

        return $next($request);
    }
}
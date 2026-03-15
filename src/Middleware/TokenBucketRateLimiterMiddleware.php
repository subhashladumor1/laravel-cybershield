<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TokenBucketRateLimiterMiddleware
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
        $key = 'token_bucket:' . real_ip(); $bucket = Cache::get($key, ['tokens' => 10, 'last_refill' => time()]); $refill = (time() - $bucket['last_refill']) * 1; $bucket['tokens'] = min(10, $bucket['tokens'] + $refill); if ($bucket['tokens'] < 1) shield_abort(429, 'Token Bucket Empty', 'TokenBucketRateLimiter'); $bucket['tokens']--; $bucket['last_refill'] = time(); Cache::put($key, $bucket, 60);

        return $next($request);
    }
}
<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectIpFingerprintMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.network_security', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        $key = 'ip_fp:' . real_ip(); $fp = get_request_fingerprint(); if (Cache::get($key) && Cache::get($key) !== $fp) { log_threat_event('ip_fingerprint_mismatch'); } Cache::put($key, $fp, 3600);

        return $next($request);
    }
}
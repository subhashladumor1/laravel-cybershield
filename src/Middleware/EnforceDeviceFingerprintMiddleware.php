<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnforceDeviceFingerprintMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.auth_security', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        if (auth()->check() && session('device_fp') !== get_request_fingerprint()) { log_threat_event('device_fingerprint_mismatch'); }

        return $next($request);
    }
}
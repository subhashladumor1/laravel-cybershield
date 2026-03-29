<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectMultipleIpLoginMiddleware
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
        $key = 'user_ips:' . auth()->id(); $ips = Cache::get($key, []); if (!in_array(real_ip(), $ips)) { if(count($ips) > 3) log_threat_event('multiple_ip_login_detected'); $ips[] = real_ip(); Cache::put($key, array_slice($ips, -5), 86400); }

        return $next($request);
    }
}

<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectRegionBlockMiddleware
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
        $region = ip_region(); if (in_array($region, shield_config('network_security.blocked_regions', []))) shield_abort(403, 'Region Blocked', 'DetectRegionBlock');

        return $next($request);
    }
}
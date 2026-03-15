<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectAbnormalTrafficMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.threat_detection', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        if (get_ip_velocity() > 300) shield_abort(403, 'Abnormal Traffic Volume Blocked', 'DetectAbnormalTraffic');

        return $next($request);
    }
}
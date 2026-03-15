<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectXssAttackMiddleware
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
        foreach($request->all() as $v) { if(is_string($v) && is_xss_injection($v)) { log_threat_event('xss_attempt'); if(shield_config('threat_detection.xss_attack')) shield_abort(403, 'XSS Attack Attempt Blocked', 'DetectXssAttack'); } }

        return $next($request);
    }
}
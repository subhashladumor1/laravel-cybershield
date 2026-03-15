<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VerifyApiOriginMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.api_security', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        $origin = $request->header('Origin'); if($origin && !in_array($origin, shield_config('api_security.allowed_origins', []))) shield_abort(403, 'Invalid API Origin', 'VerifyApiOrigin');

        return $next($request);
    }
}
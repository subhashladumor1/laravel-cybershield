<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnforceSessionIntegrityMiddleware
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
        if (get_session_entropy() < 2.5) { force_logout(); shield_abort(403, 'Low Entropy Session Blocked', 'EnforceSessionIntegrity'); }

        return $next($request);
    }
}
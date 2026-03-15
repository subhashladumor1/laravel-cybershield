<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ValidateTrustedHostMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.request_security', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        $host = $request->getHost(); if (!in_array($host, shield_config('request_security.trusted_hosts', ['localhost']))) { shield_abort(403, 'Untrusted Request Host', 'ValidateTrustedHost'); }

        return $next($request);
    }
}
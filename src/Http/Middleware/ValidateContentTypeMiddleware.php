<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ValidateContentTypeMiddleware
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
        $allowed = shield_config('request_security.allowed_content_types', ['application/json']); if ($request->header('Content-Type') && !in_array(explode(';', $request->header('Content-Type'))[0], $allowed)) { shield_abort(415, 'Unsupported Content-Type', 'ValidateContentType'); }

        return $next($request);
    }
}

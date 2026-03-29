<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VerifyApiVersionMiddleware
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
        $v = $request->header('Accept-Version'); if ($v && $v < 'v1') shield_abort(400, 'Unsupported API Version', 'VerifyApiVersion');

        return $next($request);
    }
}

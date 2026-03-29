<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectCookieLessBotMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.bot_protection', true)) {
            return $next($request);
        }

        // 2. Execute Middleware Specific Logic
        if ($request->isMethod('POST') && count($request->cookies) === 0) { log_threat_event('cookieless_bot_interaction'); }

        return $next($request);
    }
}

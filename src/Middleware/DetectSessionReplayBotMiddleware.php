<?php

namespace CyberShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetectSessionReplayBotMiddleware
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
        if (Cache::has('replay:'.session()->getId())) { shield_abort(403, 'Session Replay Attempt Blocked', 'DetectSessionReplayBot'); } Cache::put('replay:'.session()->getId(), true, 5);

        return $next($request);
    }
}
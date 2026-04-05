<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectBotMiddleware
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
        if (!shield_config('enabled', true) || !shield_config('modules.bot_protection', true)) {
            return $next($request);
        }

        if (is_bot() || is_headless() || is_scraper() || is_malicious_user_agent()) {
            shield_abort(403, "Access denied: Bot activity detected.", 'DetectBot');
        }

        return $next($request);
    }
}

<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;

class DetectHoneypotBotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @throws SecurityException
     */
    public function handle(Request $request, Closure $next)
    {
        if (!shield_config('bot_protection.honeypot.enabled', true)) {
            return $next($request);
        }

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $fieldName = shield_config('bot_protection.honeypot.field_name', 'my_hp_field');
            
            if ($request->has($fieldName) && $request->filled($fieldName)) {
                $this->handleBot($request);
            }
        }

        return $next($request);
    }

    protected function handleBot(Request $request)
    {
        \CyberShield\Logging\SecurityLogger::warning('bot', [
            'status' => 403,
            'message' => 'Bot detected via honeypot.',
        ]);

        shield_abort(403, "Bot detected via honeypot.", 'DetectHoneypotBot');
    }
}


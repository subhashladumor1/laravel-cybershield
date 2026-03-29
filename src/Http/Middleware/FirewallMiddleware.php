<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;
use CyberShield\Security\Firewall\IPManager;

class FirewallMiddleware
{
    protected $ipManager;

    public function __construct(IPManager $ipManager)
    {
        $this->ipManager = $ipManager;
    }

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
        $ip = $request->ip();

        if ($this->ipManager->isWhitelisted($ip)) {
            return $next($request);
        }

        if ($this->ipManager->isBlacklisted($ip)) {
            $this->handleBlocked($request, 'IP Blacklisted');
        }

        return $next($request);
    }

    protected function handleBlocked(Request $request, string $reason)
    {
        shield_abort(403, "Access denied by firewall.", 'Firewall');
    }
}


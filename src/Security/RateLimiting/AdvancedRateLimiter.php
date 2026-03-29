<?php

namespace CyberShield\Security\RateLimiting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use CyberShield\Exceptions\SecurityException;
use CyberShield\Security\RateLimiting\Strategies\LinearStrategy;
use CyberShield\Security\RateLimiting\Strategies\ExponentialStrategy;
use CyberShield\Security\RateLimiting\Strategies\FibonacciStrategy;
use CyberShield\Security\RateLimiting\Strategies\RateLimitStrategy;

class AdvancedRateLimiter
{
    /**
     * Check the rate limit for the given request.
     *
     * @param Request $request
     * @param string $type The type of limit (e.g., 'ip', 'login', 'registration')
     * @throws SecurityException
     */
    public function check(Request $request, string $type = 'ip')
    {
        $key = $this->resolveKey($request, $type);
        $config = shield_config("rate_limiting.{$type}", shield_config('rate_limiting.ip_limit_details'));
        
        if (!$this->checkKey($key, $config)) {
            $message = $config['message'] ?? "Too many requests. Please slow down.";
            shield_abort(429, $message, "RateLimit:{$type}");
        }
    }

    /**
     * Check the rate limit for a specific key.
     */
    public function checkKey(string $key, array $config): bool
    {
        $baseLimit = $config['limit'] ?? 60;
        $window = $config['window'] ?? 60;
        $strategyName = $config['strategy'] ?? 'linear';
        
        $attempts = Cache::get($key, 0);

        $strategy = $this->getStrategy($strategyName);
        $effectiveLimit = $strategy->calculate($attempts, $baseLimit, $window);

        if ($attempts >= $effectiveLimit) {
            return false;
        }

        Cache::put($key, $attempts + 1, $window);
        return true;
    }

    /**
     * Get the strategy instance by name.
     *
     * @param string $name
     * @return RateLimitStrategy
     */
    protected function getStrategy(string $name): RateLimitStrategy
    {
        switch ($name) {
            case 'exponential':
                return new ExponentialStrategy();
            case 'fibonacci':
                return new FibonacciStrategy();
            default:
                return new LinearStrategy();
        }
    }

    /**
     * Resolve the cache key for the request and type.
     *
     * @param Request $request
     * @param string $type
     * @return string
     */
    protected function resolveKey(Request $request, string $type)
    {
        $id = $request->user()?->id ?: $request->ip();
        return "cybershield:rate_limit:{$type}:" . md5($id);
    }
}


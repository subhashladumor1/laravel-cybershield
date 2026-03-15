<?php

namespace CyberShield\ApiSecurity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimiter
{
    /**
     * Check rate limits for the request.
     */
    public function check(Request $request): void
    {
        $this->checkConcurrentRequests($request);
        $this->applyStandardRateLimit($request);
        $this->applyCostBasedRateLimit($request);
    }

    /**
     * Limit concurrent requests from the same API Key.
     */
    protected function checkConcurrentRequests(Request $request): void
    {
        $keyRecord = $request->get('_api_key_record');
        if (!$keyRecord) {
            return;
        }

        $key = "cybershield:concurrent:{$keyRecord->key}";
        $maxConcurrent = (int) ($keyRecord->max_concurrent ?? shield_config('api_security.default_concurrent_limit', 5));

        $current = Cache::increment($key);

        if ($current > $maxConcurrent) {
            Cache::decrement($key);
            shield_abort(429, 'Too many concurrent requests', 'ConcurrentLimitExceeded');
        }

        // Set TTL to 1 hour to prevent indefinite leaks if terminate() fails
        Cache::put($key . ':timer', true, 3600);

        // Register a callback to decrement when request finishes
        $request->attributes->set('_cybershield_decrement_concurrent', $key);
    }

    /**
     * Apply standard rate limiting.
     */
    protected function applyStandardRateLimit(Request $request): void
    {
        $keyRecord = $request->get('_api_key_record');
        $identifier = $keyRecord ? (string) $keyRecord->key : real_ip();

        $limit = (int) shield_config('rate_limiting.api_limit', 1000);
        $window = (int) shield_config('rate_limiting.window', 60);

        if (RateLimiter::tooManyAttempts("api:{$identifier}", $limit)) {
            shield_abort(429, 'API Rate limit exceeded', 'ApiRateLimitExceeded');
        }

        RateLimiter::hit("api:{$identifier}", $window);
    }

    /**
     * Apply cost-based throttling.
     * Some endpoints might be marked as expensive.
     */
    protected function applyCostBasedRateLimit(Request $request): void
    {
        // Configuration can define costs for specific patterns
        $costs = (array) shield_config('api_security.endpoint_costs', [
            'api/v1/search' => 5,
            'api/v1/heavy-report' => 20,
        ]);

        $cost = 1;
        foreach ($costs as $pattern => $amount) {
            if ($request->is((string) $pattern)) {
                $cost = (int) $amount;
                break;
            }
        }

        if ($cost <= 1) {
            return;
        }

        $keyRecord = $request->get('_api_key_record');
        $identifier = $keyRecord ? (string) $keyRecord->key : real_ip();

        $totalCostLimit = (int) shield_config('api_security.daily_cost_limit', 5000);
        $cacheKey = "cybershield:cost:{$identifier}:" . date('Y-m-d');

        $currentCost = (int) Cache::get($cacheKey, 0);

        if ($currentCost + $cost > $totalCostLimit) {
            shield_abort(429, 'API budget (cost limit) exceeded for today', 'ApiCostLimitExceeded');
        }

        Cache::increment($cacheKey, $cost);
        Cache::expireAt($cacheKey, now()->endOfDay());
    }

    /**
     * Decrement concurrent count.
     */
    public function releaseConcurrent(Request $request): void
    {
        if ($key = $request->attributes->get('_cybershield_decrement_concurrent')) {
            $current = (int) Cache::get((string) $key, 0);
            if ($current > 0) {
                Cache::decrement((string) $key);
            }
        }
    }
}

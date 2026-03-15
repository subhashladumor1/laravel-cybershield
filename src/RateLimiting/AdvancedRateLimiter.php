<?php

namespace CyberShield\RateLimiting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use CyberShield\Exceptions\SecurityException;

class AdvancedRateLimiter
{
    public function check(Request $request)
    {
        $key = $this->resolveKey($request);
        $limit = config('cybershield.rate_limiting.ip_limit', 100);
        $window = config('cybershield.rate_limiting.window', 60);

        $current = Cache::get($key, 0);

        if ($current >= $limit) {
            throw new SecurityException("Rate limit exceeded", 429);
        }

        Cache::put($key, $current + 1, $window);
    }

    protected function resolveKey(Request $request)
    {
        return 'cybershield:rate_limit:' . $request->ip();
    }
}

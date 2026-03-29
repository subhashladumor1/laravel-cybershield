<?php

namespace CyberShield\Security\RateLimiting\Strategies;

class ExponentialStrategy implements RateLimitStrategy
{
    public function calculate(int $attempts, int $baseLimit, int $window): int
    {
        // Exponential backoff: limit decreases as attempts increase
        // Example: baseLimit / (2 ^ (attempts / baseLimit))
        // Or more simply, if attempts > baseLimit, increase the window or decrease the effective limit
        
        if ($attempts < $baseLimit) {
            return $baseLimit;
        }

        $factor = floor($attempts / $baseLimit);
        return max(1, (int) ($baseLimit / pow(2, $factor)));
    }

    public function getName(): string
    {
        return 'exponential';
    }
}


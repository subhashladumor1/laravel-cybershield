<?php

namespace CyberShield\Security\RateLimiting\Strategies;

class LinearStrategy implements RateLimitStrategy
{
    public function calculate(int $attempts, int $baseLimit, int $window): int
    {
        // Linear strategy: the limit stays constant
        return $baseLimit;
    }

    public function getName(): string
    {
        return 'linear';
    }
}


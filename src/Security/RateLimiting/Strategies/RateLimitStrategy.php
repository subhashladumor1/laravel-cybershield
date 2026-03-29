<?php

namespace CyberShield\Security\RateLimiting\Strategies;

interface RateLimitStrategy
{
    /**
     * Calculate the delay or next limit based on current attempts.
     *
     * @param int $attempts
     * @param int $baseLimit
     * @param int $window
     * @return int
     */
    public function calculate(int $attempts, int $baseLimit, int $window): int;

    /**
     * Get the name of the strategy.
     *
     * @return string
     */
    public function getName(): string;
}


<?php

namespace CyberShield\Security\RateLimiting\Strategies;

class FibonacciStrategy implements RateLimitStrategy
{
    protected $fib = [1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144];

    public function calculate(int $attempts, int $baseLimit, int $window): int
    {
        if ($attempts < $baseLimit) {
            return $baseLimit;
        }

        $index = (int) floor($attempts / $baseLimit);
        $divisor = $this->fib[min($index, count($this->fib) - 1)];
        
        return max(1, (int) ($baseLimit / $divisor));
    }

    public function getName(): string
    {
        return 'fibonacci';
    }
}


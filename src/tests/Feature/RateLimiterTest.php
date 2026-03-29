<?php

namespace CyberShield\Tests\Feature;

use CyberShield\Tests\TestCase;
use CyberShield\Security\RateLimiting\AdvancedRateLimiter;
use Illuminate\Support\Facades\Cache;

class RateLimiterTest extends TestCase
{
    protected $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rateLimiter = new AdvancedRateLimiter();
    }

    public function test_linear_strategy_limits_requests()
    {
        $key = 'test_linear_ip';
        $config = ['limit' => 5, 'window' => 60, 'strategy' => 'linear'];

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($this->rateLimiter->checkKey($key, $config));
        }

        $this->assertFalse($this->rateLimiter->checkKey($key, $config));
    }

    public function test_exponential_strategy_increases_delay()
    {
        $key = 'test_exponential_ip';
        $config = ['limit' => 2, 'window' => 60, 'strategy' => 'exponential'];

        $this->assertTrue($this->rateLimiter->checkKey($key, $config));
        $this->assertTrue($this->rateLimiter->checkKey($key, $config));
        
        $this->assertFalse($this->rateLimiter->checkKey($key, $config));
    }

    public function test_fibonacci_strategy_follows_sequence()
    {
        $key = 'test_fibonacci_ip';
        $config = ['limit' => 3, 'window' => 60, 'strategy' => 'fibonacci'];

        $this->assertTrue($this->rateLimiter->checkKey($key, $config));
        $this->assertTrue($this->rateLimiter->checkKey($key, $config));
        $this->assertTrue($this->rateLimiter->checkKey($key, $config));
        
        $this->assertFalse($this->rateLimiter->checkKey($key, $config));
    }
}


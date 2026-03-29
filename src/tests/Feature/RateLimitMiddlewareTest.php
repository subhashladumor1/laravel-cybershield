<?php

namespace CyberShield\Tests\Feature;

use CyberShield\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use CyberShield\Http\Middleware\FirewallMiddleware; // Using existing firewall as base for rate limit
use Illuminate\Support\Facades\Cache;

class RateLimitMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register a test route with the middleware
        Route::middleware('cybershield.firewall')->get('/test-limit', function () {
            return response('OK', 200);
        });
    }

    public function test_firewall_middleware_blocks_too_many_requests()
    {
        $ip = '10.0.0.1';
        
        // Mock the rate limiter to return false (limit exceeded)
        $this->mock(\CyberShield\Security\RateLimiting\AdvancedRateLimiter::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(false);
        });

        $response = $this->get('/test-limit', ['REMOTE_ADDR' => $ip]);

        $response->assertStatus(429);
    }
}


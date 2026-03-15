<?php

namespace CyberShield\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CyberShield\Firewall\WAFEngine;
use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;
use Mockery;

class FirewallTest extends TestCase
{
    protected $waf;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we are testing in isolation, we need to mock some things or use a helper to load signatures
        $this->waf = new WAFEngine();
    }

    public function test_it_detects_sql_injection()
    {
        $request = Request::create('/test', 'GET', ['id' => "1' OR '1'='1"]);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Potential sql attack detected');

        $this->waf->inspect($request);
    }

    public function test_it_detects_xss()
    {
        $request = Request::create('/test', 'POST', ['comment' => '<script>alert(1)</script>']);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Potential xss attack detected');

        $this->waf->inspect($request);
    }
}

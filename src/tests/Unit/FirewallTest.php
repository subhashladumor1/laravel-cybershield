<?php

namespace CyberShield\Tests\Unit;

use CyberShield\Tests\TestCase;
use CyberShield\Security\Firewall\WAFEngine;
use CyberShield\Security\Signatures\SignatureLoader;
use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;

class FirewallTest extends TestCase
{
    protected $waf;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we are testing in isolation, we need to mock some things or use a helper to load signatures
        $this->waf = new WAFEngine(new SignatureLoader(realpath(__DIR__ . '/../../Signatures')));
    }

    public function test_it_detects_sql_injection()
    {
        $request = Request::create('/test', 'GET', ['id' => "1' UNION SELECT * FROM users--"]);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Potential Basic SQL Injection attack detected in query');

        $this->waf->inspect($request);
    }

    public function test_it_detects_xss()
    {
        $request = Request::create('/test', 'POST', ['comment' => '<script>alert(1)</script>']);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('attack detected in body');

        $this->waf->inspect($request);
    }
}


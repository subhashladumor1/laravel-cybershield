<?php

namespace CyberShield\Tests\Feature\Security\Firewall;

use CyberShield\Tests\TestCase;
use CyberShield\Security\Firewall\WAFEngine;
use CyberShield\Security\Signatures\SignatureLoader;
use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;
use CyberShield\Logging\SecurityLogger;
use Illuminate\Support\Facades\Facade;

class WAFEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mock SecurityLogger to avoid file I/O errors during tests
        Facade::clearResolvedInstance('CyberShield\Logging\LogManager');
        $this->mock(SecurityLogger::class);
    }

    public function test_it_detects_sqli_attack()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $engine = new WAFEngine($loader);

        $request = Request::create('/test', 'GET', ['id' => "1' UNION SELECT * FROM users--"]);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Potential Basic SQL Injection attack detected in query');

        $engine->inspect($request);
    }

    public function test_it_detects_xss_attack_in_post()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $engine = new WAFEngine($loader);

        $request = Request::create('/test', 'POST', [], [], [], [], '<script>alert(1)</script>');
        // Manually set post data since Request::create doesn't populate it from content for POST unless content type is set
        $request->merge(['comment' => '<script>alert(1)</script>']);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('attack detected in body');

        $engine->inspect($request);
    }

    public function test_it_detects_traversal_attack()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $engine = new WAFEngine($loader);

        $request = Request::create('/test', 'GET', ['file' => '../../etc/passwd']);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Potential Directory Traversal attack detected in query');

        $engine->inspect($request);
    }

    public function test_it_ignores_safe_requests()
    {
        $loader = new SignatureLoader(realpath(__DIR__ . '/../../../../Signatures'));
        $engine = new WAFEngine($loader);

        $request = Request::create('/test', 'GET', ['id' => '123', 'name' => 'John Doe']);

        try {
            $engine->inspect($request);
            $this->assertTrue(true); // No exception thrown
        } catch (SecurityException $e) {
            $this->fail('WAFEngine threw an exception for a safe request: ' . $e->getMessage());
        }
    }
}

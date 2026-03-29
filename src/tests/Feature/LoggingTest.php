<?php

namespace CyberShield\Tests\Feature;

use CyberShield\Tests\TestCase;
use CyberShield\Logging\SecurityLogger;
use Illuminate\Support\Facades\File;

class LoggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(storage_path('logs/cybershield'));
    }

    public function test_it_can_log_to_request_channel()
    {
        SecurityLogger::log('request', ['message' => 'Test log message', 'status' => 200]);

        $logFile = storage_path('logs/cybershield/request.log');
        $this->assertTrue(File::exists($logFile));
        $this->assertStringContainsString('Test log message', File::get($logFile));
        $this->assertStringContainsString('200', File::get($logFile));
    }

    public function test_it_rotates_logs_on_max_size()
    {
        config(['cybershield.logging.max_size' => 10]); // Very small for testing

        SecurityLogger::log('request', ['message' => 'Small log']);
        SecurityLogger::log('request', ['message' => 'This should trigger rotation']);

        $logDir = storage_path('logs/cybershield');
        $files = File::files($logDir);
        
        $this->assertGreaterThan(1, count($files));
    }

    public function test_it_enriches_logs_with_context()
    {
        SecurityLogger::log('request', ['message' => 'Context test']);

        $logFile = storage_path('logs/cybershield/request.log');
        $content = File::get($logFile);

        $this->assertStringContainsString('127.0.0.1', $content); // Default IP in test
        $this->assertStringContainsString('Guest', $content);      // Default user in test
    }
}

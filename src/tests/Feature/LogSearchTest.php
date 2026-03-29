<?php

namespace CyberShield\Tests\Feature;

use CyberShield\Tests\TestCase;
use CyberShield\Logging\SecurityLogger;
use Illuminate\Support\Facades\File;

class LogSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        File::deleteDirectory(storage_path('logs/cybershield'));
        
        SecurityLogger::log('request', ['message' => 'First message', 'ip' => '1.1.1.1', 'status' => 200]);
        SecurityLogger::log('request', ['message' => 'Second message', 'ip' => '2.2.2.2', 'status' => 403]);
    }

    public function test_it_can_filter_by_ip()
    {
        $response = $this->get(route('cybershield.logs.index', ['channel' => 'request', 'ip' => '1.1.1.1']));

        $response->assertStatus(200);
        $response->assertSee('First message');
        $response->assertDontSee('Second message');
    }

    public function test_it_can_filter_by_status()
    {
        $response = $this->get(route('cybershield.logs.index', ['channel' => 'request', 'status' => '403']));

        $response->assertStatus(200);
        $response->assertSee('Second message');
        $response->assertDontSee('First message');
    }

    public function test_it_can_search_by_keyword()
    {
        $response = $this->get(route('cybershield.logs.index', ['channel' => 'request', 'keyword' => 'First']));

        $response->assertStatus(200);
        $response->assertSee('First message');
        $response->assertDontSee('Second message');
    }
}

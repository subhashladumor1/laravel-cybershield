<?php

namespace CyberShield\Tests\Feature;

use CyberShield\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use CyberShield\Http\Middleware\MonitoringMiddleware;
use CyberShield\Models\RequestLog;

class MonitoringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        \Illuminate\Support\Facades\Schema::create('cybershield_requests_logs', function ($table) {
            $table->id();
            $table->string('ip');
            $table->string('url');
            $table->string('method');
            $table->integer('status_code');
            $table->float('response_time');
            $table->string('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        \Illuminate\Support\Facades\Schema::create('cybershield_ip_activity', function ($table) {
            $table->id();
            $table->string('ip')->unique();
            $table->integer('total_requests')->default(0);
            $table->integer('threat_score')->default(0);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });

        Route::middleware(MonitoringMiddleware::class)->get('/test-monitor', function () {
            return response('OK', 200);
        });
    }

    public function test_middleware_logs_requests_to_database()
    {
        $this->get('/test-monitor');

        $this->assertDatabaseHas('cybershield_requests_logs', [
            'url' => 'test-monitor',
            'method' => 'GET',
            'status_code' => 200,
        ]);
    }

    public function test_monitoring_service_tracks_ip_activity()
    {
        $this->get('/test-monitor');

        $this->assertDatabaseHas('cybershield_ip_activity', [
            'ip' => '127.0.0.1',
        ]);
        
        $activity = \CyberShield\Models\IpActivity::where('ip', '127.0.0.1')->first();
        $this->assertEquals(1, $activity->total_requests);
    }
}

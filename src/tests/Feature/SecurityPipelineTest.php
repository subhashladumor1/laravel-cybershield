<?php

namespace CyberShield\Tests\Feature;

use Orchestra\Testbench\TestCase;
use CyberShield\CyberShieldServiceProvider;
use Illuminate\Support\Facades\Route;

class SecurityPipelineTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CyberShieldServiceProvider::class];
    }

    public function test_security_middleware_is_applied()
    {
        Route::get('/protected', function () {
            return 'OK';
        })->middleware('cybershield.firewall');

        $response = $this->get('/protected?id=union select');

        $response->assertStatus(403);
        $response->assertJsonStructure(['error', 'message', 'ip', 'timestamp']);
    }
}
